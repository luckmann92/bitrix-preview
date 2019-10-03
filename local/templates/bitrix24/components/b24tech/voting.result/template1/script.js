$(document).ready(function(){
	var button = $('.js-voiting-button'),
		results = $('.js-results'),
		editResults = $('.js-results-edit');

	if (!!button) {
		button.on('click', function() {
			if ('block' == results.css('display')) {
				button.find('.webform-small-button-text').text('Скрыть ввод результатов');
			} else {
				button.find('.webform-small-button-text').text('Ввести результаты голосования');
			}
			results.toggle('display');
			editResults.toggle('display');
		});
	}
});