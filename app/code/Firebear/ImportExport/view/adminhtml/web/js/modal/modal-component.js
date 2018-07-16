/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'mage/storage',
    'uiRegistry',
    'mage/translate'
], function ($, Parent, storage, reg, $t) {
    'use strict';

    return Parent.extend({
        defaults: {
            url: '',
            urlAjax: '',
            beforeUrl: '',
            job: 0,
            loading: false,
            template: 'Firebear_ImportExport/form/modal/modal-component',
            editUrl:'',
            isJob:0,
            end:0,
            isNotice: true,
            isError: false,
            href:'',
            isHref: false,
            notice:$t('Job saved successfully - please click Run button for launch'),
            error:$t('Error')
        },
        actionRun: function () {
            this.isNotice(false);
            this.isError(false);
            $(".debug").html('');
            var job = reg.get(this.job).data.entity_id;
            if (job == '') {
                job = localStorage.getItem('jobId');
                this.isJob = 1;
            }
            var berforeUrl = this.beforeUrl + '?id=' + job;
            var ajaxSend = this.ajaxSend.bind(this);
            this.getFile(berforeUrl).then(ajaxSend);
        },
        initObservable: function () {
            this._super()
                .observe('loading isNotice notice isHref href error isError');
            return this;
        },
        ajaxSend: function(file) {
            this.end = 0;
            var job = reg.get(this.job).data.entity_id;
            if (localStorage.getItem('jobId')) {
                job = localStorage.getItem('jobId');
            }
            var object = reg.get(this.name + '.debugger.debug');
            var url = this.url + '?form_key='+ window.FORM_KEY;
            var urlAjax = this.urlAjax + '?file=' + file;
            $('.run').attr("disabled", true);
            var self = this;
            this.loading(true);
            storage.post(
                url,
                JSON.stringify({id: job, file: file})
            ).done(
                function (response) {
                    self.end = 1;
                    object.value(response.result);
                    $(".run").attr("disabled", false);
                    self.loading(false);
                    self.isNotice(response.result);
                    self.notice($t('The process is over'));
                    self.isError(!response.result);
                    if (response.file) {
                        self.isHref(response.result);
                        self.href(response.file);
                    }

                }
            ).fail(
                function (response) {
                    self.end = 1;
                    $(".run").attr("disabled", false);
                    self.loading(false);
                    self.isNotice(false);
                    self.isError(true);
                }
            );
            if (self.end != 1) {
                setTimeout(function () {self.getDebug(urlAjax)}, 1500);
            }
        },
        getDebug: function(urlAjax) {
            var object = reg.get(this.name + '.debugger.debug');
            var self = this;
            $.get(urlAjax).done( function (response) {
                var array = response.console;
                object.value(array);
                $(".debug").scrollTop($(".debug")[0].scrollHeight);
                if (self.end != 1) {
                    setTimeout(self.getDebug(urlAjax), 1500);
                }
            });
        },
        getFile:function(beforeUrl) {
            var object = $.Deferred();
            var file = '';
            storage.get(
                beforeUrl
            ).done(
                function (response) {
                    file = response;
                    object.resolve(file);
                }
            ).fail(
                function (response) {
                   file = null;
                    object.resolve(file);
                }
            );
            return object.promise();
        },
        toggleModal: function () {
            this._super();
           // this.isNotice(false);
            this.isHref(false);
            this.isError(false);
            $(".debug").html('');
        },
        /**
         * Close moda
         */
        closeModal: function () {
            this._super();
            this.notice('Job saved successfully - please click Run button for launch');
            if (this.isJob) {
                location.href = this.editUrl + 'entity_id/' + localStorage.getItem('jobId');
            }
        },
    });
});