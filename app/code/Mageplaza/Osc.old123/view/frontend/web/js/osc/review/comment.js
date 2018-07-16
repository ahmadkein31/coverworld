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
define(["prototype", "validation"], function () {
    /* Review Comment Class*/
    MageplazaOscReviewComment = Class.create();
    MageplazaOscReviewComment.prototype = {
        initialize: function (config) {
            var config = config || {};
            this.useEffect = config.useEffect || false;
            this.commentContainer = config.commentContainer ? config.commentContainer : '.one-step-checkout-review-comments';
            this.commentContainerEl = $$(this.commentContainer).first();
            this.newRowCount = config.newRowCount || 5;
            this.commentContainerEl.select('textarea').each(function (textarea) {
                textarea.setStyle({
                    'overflow-y': 'hidden'
                });
                this.textAreaEffectObserver(textarea);
            }.bind(this));
            Form.getElements(this.commentContainerEl).each(function (element) {
                element.observe('change', function () {
                    this.saveFormData();
                }.bind(this));
            }.bind(this));
        },

        saveFormData: function () {
            MageplazaOsc.saveFormData(this.commentContainerEl);
        },
        textAreaEffectObserver: function (textarea) {
            if (!this.useEffect)
                return false;
            var orgScrollHeight = textarea.scrollHeight;
            var orgRowCount = parseInt(textarea.getAttribute('rows'));
            var orgHeight = parseInt(textarea.getStyle('height'));
            this._focusEffect(textarea, orgScrollHeight, orgRowCount, orgHeight);
            this._blurEffect(textarea, orgScrollHeight, orgRowCount, orgHeight);
        },
        _focusEffect: function (textarea, orgScrollHeight, orgRowCount, orgHeight) {
            textarea.observe('focus', function () {
                var currentRowCount = orgRowCount +
                    (((textarea.scrollHeight - orgScrollHeight) * orgRowCount) / orgHeight);
                if (currentRowCount < this.newRowCount) {
                    currentRowCount = this.newRowCount;
                } else {
                    currentRowCount++; //add on empty line
                }
                var currentHeight = (orgHeight / orgRowCount) * currentRowCount;
                this.rowsAttributeEffect(textarea, currentRowCount, currentHeight, function () {
                    textarea.setStyle({
                        'overflow-y': 'auto'
                    });
                });
            }.bind(this));
        },
        _blurEffect: function (textarea, orgScrollHeight, orgRowCount, orgHeight) {
            textarea.observe('blur', function () {
                var lengthOfValue = textarea.getValue().strip().length;
                if (lengthOfValue === 0) {
                    this.scrollTextareaEffect(textarea, function () {
                        textarea.setStyle({
                            'overflow-y': 'hidden'
                        });
                        this.rowsAttributeEffect(textarea, orgRowCount, orgHeight);
                    }.bind(this));
                } else {
                    var newHeight = (orgHeight / orgRowCount) * this.newRowCount;
                    this.scrollTextareaEffect(textarea, function () {
                        textarea.setStyle({
                            'overflow-y': 'hidden'
                        });
                        this.rowsAttributeEffect(textarea, this.newRowCount, newHeight);
                    }.bind(this));
                }
            }.bind(this));
        },
        rowsAttributeEffect: function (textArea, newRows, newHeight, afterFinish) {
            if (textArea.effect) {
                textArea.effect.cancel();
            }
            this._textAraEffect(textArea, newRows, newHeight, afterFinish);
        },
        scrollTextareaEffect: function (textarea, afterFinish) {
            var textEffect = textarea.effect;
            if (textEffect) {
                textEffect.cancel();
            }
            this._scrollEffect(textarea, afterFinish);

        },
        _textAraEffect: function (textArea, newRows, newHeight, afterFinish) {
            var afterFinish = afterFinish || new Function();
            textArea.effect = new Effect.Morph(textArea, {
                style: {
                    height: newHeight + "px"
                },
                duration: 0.5,
                afterFinish: function () {
                    textArea.setAttribute('rows', newRows);
                    delete textArea.effect;
                    afterFinish();
                }
            });
        },
        _scrollEffect: function (textArea, afterFinish) {
            var afterFinish = afterFinish || new Function();
            if (textArea.scrollTop === 0) {
                afterFinish();
                return;
            }
            new Effect.Tween(textArea, textArea.scrollTop, 0, {
                duration: 0.5,
                afterFinish: function () {
                    afterFinish();
                }
            }, 'scrollTop');
        }
    };
    return MageplazaOscReviewComment;
});


