'use strict';

(function ($) {
	$.fn.blogPosts = function (options) {
		const settings = $.extend({
			api: '',
			action: 'blog_posts',
			loading_text: 'Loading',
			list_type: 'ul'
		}, options);

		if ('' === settings.api) {
			return this;
		}

		function generateList(data) {
			let output = '';

			if (0 === data.length) {
				return output;
			}

			data.forEach(function (item) {
				output += '<li><a href="' + item.url + '" target="_blank">' + item.title + '</a></li>';
			});
			return $('<' + settings.list_type + '/>').append(output);
		}

		return this.each(function () {
			if ($(this).length < 1) {
				return;
			}

			const $wrapper = $(this);
			$.ajax({
				url: settings.api,
				type: 'GET',
				dataType: 'json',
				data: {
					action: settings.action
				},

				beforeSend() {
					$wrapper.html(settings.loading_text);
				},

				complete(jqXHR) {
					const response = JSON.parse(jqXHR.responseText);
					$wrapper.html('');

					if (true === response.success) {
						const listMarkup = generateList(response.data);
						$wrapper.append(listMarkup);
					}
				}

			});
		});
	};

	$(document).ready(function () {
		$('.ns-blog-list').blogPosts({
			api: ajaxurl,
			action: 'sa_nsbl_get_posts'
		});
	});
})(jQuery);
