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
        onReady: function (Main) {
            this.main = Main;
            this.showMore();
        }
    };
