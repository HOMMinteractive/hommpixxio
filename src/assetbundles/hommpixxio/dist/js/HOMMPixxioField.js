/**
 HOMM pixx.io plugin for Craft CMS
 *
 * HOMMPixxioField Field JS
 *
 * @author    Benjamin Ammann
 * @copyright Copyright (c) 2024 HOMM interactive
 * @link      https://github.com/HOMMinteractive
 * @package   HOMMPixxio
 * @since     0.0.1
 */

; (function ($, window, document, undefined) {

    var pluginName = "HOMMPixxioField",
        defaults = {};

    // Plugin constructor
    function Plugin(element, options) {
        this.element = element;

        this.options = $.extend({}, defaults, options);

        this._defaults = defaults;
        this._name = pluginName;

        this.init();
    }

    Plugin.prototype = {

        init: function (id) {
            var _this = this;

            $(function () {

                /* -- _this.options gives us access to the $jsonVars that our FieldType passed down to us */

            });
        }
    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName,
                    new Plugin(this, options));
            }
        });
    };

})(jQuery, window, document);

$(function () {
    var pixxioModals = [];

    $(document).on('click', '.pixxio-modal-toggle', function () {
        var $pixxio = $(this).parents('.pixxio-field').parent();
        if ($pixxio.data('modal-id') !== undefined) {
            pixxioModals[$pixxio.data('modal-id')].show();
        } else {
            var m = $pixxio.find('.pixxio-modal');
            var modal = new Craft.PixxioModal(m, $pixxio);
            pixxioModals.push(modal);
            $pixxio.data('modal-id', pixxioModals.length - 1);
        }
    });

    // Remove the selected file
    $(document).on('click', '.pixxioField_removefile', function () {
        let $file = $(this).parents('.pixxio-field').find('.pixxio-file');

        $file.find('input[data-type="id"]').val('');
        $file.find('input[data-type="name"]').val('');
        $file.find('input[data-type="url"]').val('');
        $file.find('input[data-type="directory"]').val('');

        $file.find('figure img').attr('src', '');
        $file.find('figure img').attr('alt', '');
        $file.find('figcaption').text('Keine Datei ausgewählt...');
    });
});


Craft.PixxioModal = Garnish.Modal.extend(
    {
        file: null,
        fileField: null,
        fileSelectedClass: 'pixxio--selected',
        selectedDirectories: [],

        $selectBtn: null,
        $cancelBtn: null,

        $pageInfo: null,
        $directoryBtns: null,
        $prevPageButton: null,
        $nextPageButton: null,

        isSearching: null,
        currentPage: 1,

        init: function (container, field, settings) {
            this.fileField = field;
            this.setSettings(settings, Craft.PixxioModal.defaults);

            // Build the modal
            this.base(container, this.settings);

            this.$cancelBtn = $(container).find('.pixxio-modal_close');
            this.$selectBtn = $(container).find('.pixxio-modal_select');

            this.$searchInput = $(container).find('.search-container input');
            this.$clearSearchButton = $(container).find('.search-container .clear-btn');

            this.$pageInfo = $(container).find('.pixxio-page-info');
            this.$directoryBtns = $(container).find('[data-pixxio-type="directory"]');
            this.$prevPageButton = $(container).find('.page-link.prev-page');
            this.$nextPageButton = $(container).find('.page-link.next-page');

            this.addListener(this.$cancelBtn, 'activate', 'cancel');
            this.addListener(this.$selectBtn, 'activate', 'selectFile');

            this.addListener(this.$searchInput, 'input', 'onSearchFilesInput');
            this.addListener(this.$clearSearchButton, 'click', 'clearSearch');

            this.addListener(this.$directoryBtns, 'click', 'onRenderDirectoryTreeClick');
            this.addListener(this.$prevPageButton, 'click', 'onPrevPageButtonClick');
            this.addListener(this.$nextPageButton, 'click', 'onNextPageButtonClick');

            $(container).find('[data-pixxio-type="directory"].sel').click();
        },

        onFadeIn: function () {
            // TODO:
            // If there is already an file selected then use that one as the selected file
            // if ($(this.$container).find('.homm-pixxio [data-pixxio-type="file"].' + this.fileSelectedClass).length > 0) {
            //     this.enableSelectBtn();
            // }
        },

        onRenderDirectoryTreeClick: function (e) {
            e.preventDefault();

            this.currentPage = 1;

            let $link = $(e.target);
            if (!$link.data('id')) {
                $link = $link.closest('[data-pixxio-type="directory"]');
            }

            this.$container.find('[data-pixxio-type="directory"].sel').removeClass('sel');
            this.renderDirectoryTree({ ...$link.data(), url: $link.attr('href') });
            this.selectedDirectories.forEach((d) => {
                this.$container.find('[data-pixxio-type="directory"][data-id="' + d.id + '"]').addClass('sel');
            })
        },

        onSearchFilesInput: function (e) {
            e.preventDefault();

            this.currentPage = 1;
            this.searchFiles();
        },

        onPrevPageButtonClick: function (e) {
            e.preventDefault();

            if (this.$prevPageButton.is(':disabled')) {
                return;
            }

            this.currentPage--;

            if (this.$searchInput.val()) {
                this.searchFiles();
            } else {
                if (this.selectedDirectories.length) {
                    this.renderDirectoryTree(this.selectedDirectories[this.selectedDirectories.length - 1]);
                } else {
                    this.$directoryBtns[0].click();
                }
            }
        },

        onNextPageButtonClick: function (e) {
            e.preventDefault();

            if (this.$nextPageButton.is(':disabled')) {
                return;
            }

            this.currentPage++;

            if (this.$searchInput.val()) {
                this.searchFiles();
            } else {
                if (this.selectedDirectories.length) {
                    this.renderDirectoryTree(this.selectedDirectories[this.selectedDirectories.length - 1]);
                } else {
                    this.$directoryBtns[0].click();
                }
            }

        },

        renderPageButtons: function (response) {
            if (response.quantity) {
                this.$pageInfo.find('[data-page-start]').text(response.pageStart);
                this.$pageInfo.find('[data-page-end]').text(response.pageEnd);
                this.$pageInfo.find('[data-total-quantity]').text(response.quantity);
            } else {
                this.$pageInfo.find('[data-page-start]').text('0');
                this.$pageInfo.find('[data-page-end]').text('0');
                this.$pageInfo.find('[data-total-quantity]').text('0');
            }

            if (this.currentPage <= 1) {
                this.currentPage = 1;
                this.$prevPageButton.attr('disabled', 'disabled');
                this.$prevPageButton.addClass('disabled');
            } else {
                this.$prevPageButton.removeAttr('disabled');
                this.$prevPageButton.removeClass('disabled');
            }

            if (this.currentPage >= response.lastPage) {
                this.currentPage = response.lastPage;
                this.$nextPageButton.attr('disabled', 'disabled');
                this.$nextPageButton.addClass('disabled');
            } else {
                this.$nextPageButton.removeAttr('disabled');
                this.$nextPageButton.removeClass('disabled');
            }
        },

        enableSelectBtn: function () {
            this.$selectBtn.removeClass('disabled');
        },

        disableSelectBtn: function () {
            this.$selectBtn.addClass('disabled');
        },

        renderDirectory: function ($template, $thumbsview, { id, name, label, parentid }) {
            let $clone = $($template.html());
            let $cloneLink = $clone.find('a');
            let href = $cloneLink.attr('href').replace('{parentID}', id);

            if (!label) {
                label = name;
            }

            $cloneLink.attr('data-parentid', parentid);
            $cloneLink.attr('data-id', id);
            $cloneLink.attr('data-name', name);
            $cloneLink.attr('href', href);
            $cloneLink.attr('title', name);
            $cloneLink.find('figcaption').text(label);
            $thumbsview.append($clone);
            this.addListener($cloneLink, 'click', 'onRenderDirectoryTreeClick');
        },

        renderFile: function ($template, $thumbsview, { id, name, src, href, parentid }) {
            let $clone = $($template.html());
            let $cloneLink = $clone.find('a');

            $cloneLink.attr('data-parentid', parentid);
            $cloneLink.attr('data-id', id);
            $cloneLink.attr('data-name', name);
            $cloneLink.attr('href', href);
            $cloneLink.attr('title', name);
            $cloneLink.find('figure img').attr('src', src);
            $cloneLink.find('figure img').attr('alt', name);
            $cloneLink.find('figcaption').text(name);
            $thumbsview.append($clone);

            let dblClick = false;
            this.addListener($clone, 'click', (e) => {
                e.preventDefault();

                if (dblClick) {
                    this.selectFile();
                }

                this.enableSelectBtn();

                let $item = $(e.target).parents('section').find('[data-pixxio-type="file"][data-id="' + id + '"]');

                $item.parents('.homm-pixxio').find('[data-pixxio-type="file"]').removeClass(this.fileSelectedClass);
                $item.addClass(this.fileSelectedClass);
                this.file = {
                    url: $item.attr('href'),
                    ...$item.data(),
                };
                this.enableSelectBtn();

                dblClick = true;
                setTimeout(() => dblClick = false, 500);
            });
        },

        renderDirectoryTree: function ({ id, url, name, parentid }) {
            let directory = { id, url, name, parentid };

            let parentDirectory = null;
            let lastDirectory = null;
            if (this.selectedDirectories.length) {
                lastDirectory = this.selectedDirectories[this.selectedDirectories.length - 1];
                if (this.selectedDirectories.length >= 2) {
                    parentDirectory = this.selectedDirectories[this.selectedDirectories.length - 2];
                }

                if (!parentid) {
                    this.selectedDirectories = [directory];
                } else if (parentDirectory && parentDirectory.id == directory.id) {
                    this.selectedDirectories.pop();
                } else if (lastDirectory && lastDirectory.id == directory.id) {
                    // do nothing
                } else {
                    this.selectedDirectories.push(directory);
                }
            } else {
                this.selectedDirectories.push(directory);
            }

            if (this.selectedDirectories.length && this.selectedDirectories.length >= 2) {
                parentDirectory = this.selectedDirectories[this.selectedDirectories.length - 2];
            }

            this.showSpinner();
            let $container = this.$container.find('.homm-pixxio');
            let $thumbsview = $container.find('.thumbsview');
            $container.find('section').remove();

            let directoryResponse = false;
            let fileResponse = false;
            var self = this;
            var onFinishRequests = function () {
                if (directoryResponse === false || fileResponse === false) {
                    return;
                }

                let $template = null;

                // Directories
                $template = $container.find('template.directory');
                if (parentDirectory && directory.parentid) {
                    let parentDirectory = self.selectedDirectories[self.selectedDirectories.length - 2];
                    self.renderDirectory($template, $thumbsview, { ...parentDirectory, label: '... nach oben' });
                }

                // Files
                directoryResponse.forEach(dir => {
                    self.renderDirectory($template, $thumbsview, { ...dir, parentid: directory.id });
                });
                $template = $container.find('template.file');
                self.renderPageButtons(fileResponse);
                fileResponse.files.forEach(file => {
                    self.renderFile($template, $thumbsview, { id: file.id, name: file.fileName, src: file.previewFileURL, href: file.originalFileURL, parentid: file.directory.id });
                });

                self.hideSpinner();
            }

            $.get(directory.url, (directories) => {
                directoryResponse = directories
                onFinishRequests();
            });

            $.get(directory.url + '/files' + '?page=' + this.currentPage, (response) => {
                fileResponse = response;
                onFinishRequests(response);
            });
        },

        searchFiles: function () {
            this.showSpinner();
            clearTimeout(this.isSearching);
            this.isSearching = setTimeout(() => {
                let $container = this.$container.find('.homm-pixxio');
                let $thumbsview = $container.find('.thumbsview');

                if (!this.$searchInput.val()) {
                    this.clearSearch();
                } else {
                    if (this.selectedDirectories.length) {
                        directory = this.selectedDirectories[this.selectedDirectories.length - 1];
                    }

                    $.get(this.$searchInput.data('url') + '?page=' + this.currentPage + '&term=' + this.$searchInput.val() + '&directoryID=' + directory.id, (response) => {
                        this.renderPageButtons(response);

                        let $template = $container.find('template.file');

                        $container.find('section').remove();

                        response.files.forEach(file => {
                            this.renderFile($template, $thumbsview, { id: file.id, name: file.fileName, src: file.previewFileURL, href: file.originalFileURL, parentid: file.directory.id })
                        });

                        this.hideSpinner();
                    });
                }
            }, 300);
        },

        clearSearch: function () {
            if (this.selectedDirectories.length) {
                this.renderDirectoryTree(this.selectedDirectories[this.selectedDirectories.length - 1]);
            } else {
                this.$directoryBtns[0].click();
            }

            this.$searchInput.val('');
        },

        cancel: function () {
            this.hide();
        },

        selectFile: function () {
            let $file = $(this.fileField).find('.pixxio-file');

            $file.find('input[data-type="id"]').val(this.file.id);
            $file.find('input[data-type="name"]').val(this.file.name);
            $file.find('input[data-type="url"]').val(this.file.url);
            $file.find('input[data-type="directory"]').val(this.file.parentid);

            $file.find('figure img').attr('src', this.file.url);
            $file.find('figure img').attr('alt', this.file.name);
            $file.find('figcaption').text(this.file.name);

            this.hide();
        },

        showSpinner: function () {
            this.$container.find('.homm-pixxio.elements').addClass('busy');
            this.$container.find('.homm-pixxio .update-spinner').show();
        },

        hideSpinner: function () {
            this.$container.find('.homm-pixxio.elements').removeClass('busy');
            this.$container.find('.homm-pixxio .update-spinner').hide();
        },
    },
    {
        defaults: {
            resizable: true,
            hideOnSelect: true,
            onCancel: $.noop,
            onSelect: $.noop,
        }
    }
);
