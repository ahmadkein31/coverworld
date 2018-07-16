/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) 2016 Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define([
    "mage/url",
    "prototype"
], function (urlBuilder) {
    MageplazaOsc = Class.create();
    MageplazaOsc.prototype = {
        initialize: function () {
            this.blocks = {};
            this.queueBlocks = {};
            this.currentRequest = '';
            this.queueRequest = [];
            this.initAjaxResponse();
            this.paymentInformation = null;
        },
        setConfig: function (config) {
            this.blockMapping = config.blockMapping;
            this.loadingClass = config.loadingClass;
            this.showNumber = config.showNumber;
            this.actionPattern = config.actionPattern;
            this.isUsedMorphEffect = config.isUsedMorphEffect;
            this.form_key = config.form_key;
            this.saveFormUrl = config.saveFormUrl;

        },
        setPaymentInformation: function (paymentInfo) {
            this.paymentInformation = paymentInfo;
        },
        setActionPattern: function (pattern) {
            this.actionPattern = pattern;
        },
        initAjaxResponse: function () {
            Ajax.Responders.register({
                onComplete: function (response) {
                    if (response.transport.status === 403) {
                        document.location.reload();
                    }
                }
            });
        },
        setBlocks: function (config) {
            $H(config.blocks).each(function (el) {
                this._addBlock(el[0], el[1]);
            }.bind(this));
        },
        _addBlock: function (name, selector) {
            if (typeof(this.blocks[name]) != 'undefined') {
                return;
            }
            $$(selector).each(function (el) {
                this.blocks[name] = el;

            }.bind(this));
        },
        saveFormData: function (formContainer) {
            var params = Form.serialize(formContainer, true);
            var requestOptions = {
                method: 'post',
                parameters: Object.extend(params, {
                    form_key: this.form_key
                }),
                onCreate: function (request) {
                    Ajax.Responders.unregister(varienLoaderHandler.handler);
                }
            }
            new Ajax.Request(this.saveFormUrl, requestOptions);
        },
        applyMorphEffect: function (selector, newHeight, duration, callBack) {

            if (this.isUsedMorphEffect) {
                if (selector.effect) {
                    selector.effect.cancel();
                }
                var callBack = callBack || Prototype.emptyFunction;
                selector.effect = new Effect.Morph(selector, {
                    style: {
                        'height': newHeight + 'px'
                    },
                    duration: duration,
                    afterFinish: function () {
                        delete selector.effect;
                        callBack();
                    }
                });
            }
            else {
                if (newHeight === 0) {
                    selector.setStyle({
                        display: 'none',
                        height: 0
                    })
                }
                else {
                    selector.setStyle({
                        height: '',
                        display: ''
                    })
                }

            }

        },
        getHeightFromElement: function (element) {
            var currentHeight = element.style.height;
            var currentVisibility = element.style.visibility;
            var currentDisplay = element.style.display;
            element = this.resetElement(element);
            var height = element.getHeight();
            element.setStyle({
                'display': currentDisplay,
                'visibility': currentVisibility,
                'height': currentHeight
            });
            return height;

        },
        resetElement: function (element) {
            element.setStyle({
                'height': '',
                'display': '',
                'visibility': 'hidden'
            });
            return element;
        },
        showMessage: function (block, message, messageClass) {
            if (typeof(message) === 'object' && message.length > 0) {
                message.each(function (msg) {
                    this._appendMessage(block, msg, messageClass);
                }.bind(this));
            }
            else if (typeof(message) === 'string') {
                this._appendMessage(block, message, messageClass);
            }

        },
        removeMessage: function (block, messageClass) {
            block.select('.' + messageClass).each(function (el) {
                el.remove();
            })
        },
        _appendMessage: function (block, message, messageClass) {
            var currenMessage = null;
            var messageSection = block.select("." + messageClass + " ol");
            if (messageSection.length === 0) {
                var messageElement = new Element('div');
                messageElement.addClassName(messageClass);
                messageElement.appendChild(new Element('ol'));
                block.insertBefore(messageElement, block.down());
                currenMessage = messageElement.down();
            } else {
                currenMessage = messageSection.first();
            }
            var newMessage = new Element('li');
            newMessage.update(message);
            currenMessage.appendChild(newMessage);
        },
        addOverlay: function (block, className) {
            var overlay = new Element('div');
            overlay.addClassName(className);
            block.appendChild(overlay);
        },
        removeOverlay: function (block, className) {
            block.select('.' + className).each(function (el) {
                el.remove();
            })
        },
        Request: function (url, options) {
            var action = this._getActionFromUrl(url, this.actionPattern);
            this.addBlocksToQueue(action);
            if (this.currentRequest === '') {
                this.newRequest(url, options);
            } else {
                this.queueRequest.push([url, options]);
            }
        },
        newRequest: function (url, options) {
            var action = this._getActionFromUrl(url, this.actionPattern);
            var options = options || {};
            var requestOptions = Object.extend({}, options);
            var params = requestOptions.parameters || {};
            var self = this;
            requestOptions = Object.extend(requestOptions, {
                parameters: Object.extend(params, {
                    form_key: this.form_key
                }),
                onCreate: function (request) {
                    Ajax.Responders.unregister(varienLoaderHandler.handler);
                },
                onComplete: function (transport) {
                    self.onComplete(transport, action);
                    if (options.onComplete) {
                        options.onComplete(transport);
                    }
                }
            });
            this.currentRequest = new Ajax.Request(url, requestOptions);
        },
        reRequest: function (url, options) {
            this.newRequest(url, options);
        },
        _getActionFromUrl: function (url, pattern) {
            var matches = url.match(pattern);
            if (!matches || !matches[1]) {
                return null;
            }
            return matches[1];
        },
        addBlocksToQueue: function (action) {
            if (!action || !this.blockMapping[action]) {
                return;
            }
            this.blockMapping[action].each(function (name) {
                if (typeof(this.queueBlocks[name]) === 'undefined') {
                    this.queueBlocks[name] = 0;
                }
                if (!this.blocks[name]) {
                    return;
                }
                if (this.queueBlocks[name] === 0) {
                    var selection = this.blocks[name];
                    if ("appendQueueBeforeFinish" in this.blocks[name]) {
                        this.blocks[name].appendQueueBeforeFinish();
                    }
                    this.appendLoading(selection);
                    if ("appendQueueAfterFinish" in this.blocks[name]) {
                        this.blocks[name].appendQueueAfterFinish();
                    }
                }
                this.queueBlocks[name]++;
            }.bind(this));
        },
        appendLoading: function (block, loadingClass) {
            var className = loadingClass;
            if (!className) {
                className = this.loadingClass;
            }
            //fix minicart 1.9
            block.setStyle({
                'position': 'relative'
            })
            var ajaxLoading = new Element('div');
            ajaxLoading.addClassName(className);
            block.insertBefore(ajaxLoading, block.down());
        },

        onComplete: function (transport, action) {
            try {
                eval("var response = " + transport.responseText);
            } catch (e) {
                var response = {
                    blocks: {}
                };
            }
            if (response.quote_is_empty) {
                window.location.href = urlBuilder.build('checkout/cart/index');
                return;
            }
            this.removeBlockQueue(action, response);
            this.currentRequest = '';
            if (this.queueRequest.length > 0) {
                this._emptyQueue();
                var args = this.queueRequest.shift();
                this.reRequest(args[0], args[1]);
            }
        },
        removeBlockQueue: function (action, response) {
            if (!action || !this.blockMapping[action]) {
                return;
            }
            var response = response || {};
            var responseBlocks = response.blocks || {};
            this.blockMapping[action].each(function (blockName) {
                if (!this.blocks[blockName]) {
                    return;
                }
                if (this.queueBlocks[blockName]) {
                    this.queueBlocks[blockName]--;
                }
                if (this.queueBlocks[blockName] === 0) {
                    if (responseBlocks[blockName]) {
                        if (blockName == 'payment_method') {
                            this.paymentInformation();
                        }
                        else {
                            this.blocks[blockName].update(responseBlocks[blockName]);
                        }
                    }
                    if ("removeQueueBeforeFinish" in this.blocks[blockName]) {
                        this.blocks[blockName].removeQueueBeforeFinish(response);
                    }
                    this.updateNumbers();
                    this.removeLoading(this.blocks[blockName]);
                    if ("removeQueueAfterFinish" in this.blocks[blockName]) {
                        this.blocks[blockName].removeQueueAfterFinish(response);
                    }
                }
            }.bind(this));
        },
        removeLoading: function (block, loadingClass) {
            var className = loadingClass;
            if (!className) {
                className = this.loadingClass;
            }
            var selector = "." + className;
            block.setStyle({
                'position': ''
            })
            block.select(selector).each(function (el) {
                el.remove();
            });
        },
        _emptyQueue: function () {
            var actions = [];
            var removed = [];
            //Fix for muli click to element when processing
            this.queueRequest.reverse().each(function (args, key) {
                var action = this._getActionFromUrl(args[0], this.actionPattern);
                if (actions.indexOf(action) === -1) {
                    actions.push(action);
                } else {
                    removed.push(key);
                }
            }.bind(this));
            var newQueue = [];
            this.queueRequest.each(function (args, key) {
                var action = this._getActionFromUrl(args[0], this.actionPattern);
                if (removed.indexOf(key) === -1) {
                    newQueue.push(args);
                } else {
                    this.removeBlockQueue(action);
                }
            }.bind(this));
            this.queueRequest = newQueue.reverse();
        },
        updateNumbers: function () {
            if (!this.showNumber)
                return;
            var currentField = 1;
            $$('.one-step-checkout-number').each(function (el) {
                if (el.up().getHeight() === 0 || (el.next() && el.next().getHeight() === 0)) {
                    return false;
                }
                el.update(currentField);
                currentField++;
            });
        }
    };
    MageplazaOsc = new MageplazaOsc();
});


