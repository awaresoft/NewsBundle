/*jslint browser: true*/
/*global
 $
 */
'use strict';
var Post = Post || {
        main: null,

        showMore: function () {
            $('#news-list-more').hide();

            if ($('#news-list').length === 0 || NEWS_LIST_URL === undefined) {
                return;
            }

            var limit = $('#news-list').data('limit');
            var count = $('#news-list').data('count');
            var nextPage = 2;

            if (limit < count) {
                $('#news-list-more').show();
            }

            $('#news-list-more a ').on('click', function () {
                $.ajax({
                    type: 'GET',
                    url: NEWS_LIST_URL,
                    data: {
                        page: nextPage
                    },
                    dataType: 'json',
                    success: function (data) {
                        $("#news-list-container").append(data.template);
                        nextPage = nextPage + 1;
                    }
                });

                if (nextPage * limit >= count) {
                    $('#news-list-more').hide();
                }
            });
        },
        runGallery: function () {
            $(".grid-post.grid-justify").justifiedGallery({
                thumbnailPath: function (currentPath, width, height) {
                    if (height < 200) {
                        return currentPath;
                    } else {
                        if (width <= 250) {
                            return currentPath.replace("_normal", "_small");
                        } else if (width > 250 && width <= 500) {
                            return currentPath.replace("_normal", "_500");
                        } else if (width > 500 && width <= 720) {
                            return currentPath.replace("_normal", "_720");
                        } else if (width > 720 && width <= 850) {
                            return currentPath.replace("_normal", "_850");
                        } else {
                            return currentPath.replace("_normal", "_big");
                        }
                    }
                },
                selector: '.item',
                rowHeight: 200,
                maxRowHeight: 400,
                captions: false,
                margins: 5,
                lastRow: 'nojustify'
            }).on('jg.complete', function () {
                Post.runLightGalleryPlugin($(this));
            });
        },
        runLightGalleryPlugin: function ($element) {
            var staticOptions = {
                selector: '.open',
                download: false,
                mode: 'lg-slide',
                thumbnail: false,
            };

            $element.on('onAfterOpen.lg', function () {
                prepareGalleryView();
            });

            $element.on('onBeforeSlide.lg', function () {
                prepareGalleryView();
            });

            function prepareGalleryView() {
                $('.lg-outer .lg-object').addClass('visible');
            }

            $element.lightGallery(staticOptions);
        },
        onReady: function (Main) {
            this.main = Main;
            this.showMore();
        },
        onLoad: function (Main) {
            this.main = Main;
            this.runGallery();
        }
    };
