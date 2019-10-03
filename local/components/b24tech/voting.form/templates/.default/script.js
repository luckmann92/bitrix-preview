var VoitingBulletin = function(userId, meetingId, sectionId, mode) {
	this.btn = $('.js-submit-button');
	this.btnFinish = $('.js-finish-voting');
	this.btnAgreement= $('.js-agreement-voting');
	this.wrapper = $('#js-voting');
	this.choiceMember = $('#js-member');
	this.messagesWrapper = $('.js-messages');
	this.userId = userId;
	this.meetingId = meetingId;
	this.sectionId = sectionId;
	this.url = '/local/components/b24tech/voting.form/ajax.php';
	this.mode = mode;

	this.bindEvents();
}

VoitingBulletin.prototype.bindEvents = function() {
	this.btn.on('click', this.submitForm.bind(this));
	this.btnFinish.on('click', this.finishVoiting.bind(this));
	this.btnAgreement.on('click', this.agreementVoiting.bind(this));
	
	if(this.mode == 'offline') {
		this.choiceMember.on('change', function(r){
			this.messagesWrapper.html('').hide();
		}.bind(this));
	}
}

VoitingBulletin.prototype.submitForm = function(e) {
	var answers = this.getAnswers();
	
	if(answers.length > 0) {
		this.send(answers);
	}
}

VoitingBulletin.prototype.send = function(data) {
	
	var userId = this.userId;
	if(this.mode == 'offline') {
		userId = parseInt($('#js-member').val());
		this.messagesWrapper.html('').hide();
	}


	if(!isNaN(userId))
		$.ajax({
		  url: this.url,
		  data: {
		  	user_id: userId, 
		  	meeting_id: this.meetingId, 
		  	section_id: this.sectionId, 
		  	answers: data,
		  	action: 'save'
		  },
		  success: function(res){

		    if(res.success) {
		    	if(this.mode == 'offline') {
		    		this.messagesWrapper.html('Ваш ответ успешно принят').show();
		    	} else {
		    		this.wrapper.html('Ваш ответ успешно принят');
		    	}
		    } else {
		    	if(this.mode == 'offline') {
		    		this.messagesWrapper.html('Произошла системная ошибка обратитесь к администратору');
		    	} else {
					this.wrapper.html('Произошла системная ошибка обратитесь к администратору').show();
		    	}
		    	
		    }

		  }.bind(this)
		});
}

VoitingBulletin.prototype.getAnswers = function() {
	var answers = [];

	this.wrapper.find('input:checked').each(function(item) {
		answers.push({
			id: $(this).data('id'),
			section: $(this).data('section'),
			meeting: $(this).data('meeting'),
			instance: $(this).data('instance'),
			parent_instance: $(this).data('parent-instance'),
			title: $(this).data('title'),
			value: $(this).val(),
		});
	});

	return answers;
}

VoitingBulletin.prototype.finishVoiting = function(e) {
	$.ajax({
	  url: this.url,
	  data: {
	  	user_id: this.userId, 
	  	meeting_id: this.meetingId, 
	  	section_id: this.sectionId, 
	  	answers: [],
	  	action: 'agreement'
	  },
	  success: function(res){
 
	    console.log(res);
	    if(res.success) {
	    	this.wrapper.html('Голосование успешно завершено');
	    } else {
	    	this.wrapper.html('Произошла системная ошибка обратитесь к администратору');
	    }

	  }.bind(this)
	});
}

VoitingBulletin.prototype.agreementVoiting = function(e) {
	$.ajax({
	  url: this.url,
	  data: {
	  	user_id: this.userId, 
	  	meeting_id: this.meetingId, 
	  	section_id: this.sectionId, 
	  	answers: [],
	  	action: 'close'
	  },
	  success: function(res){

	    console.log(res);
	    if(res.success) {
	    	this.wrapper.html('Голосование успешно завершено');
	    } else {
	    	this.wrapper.html('Произошла системная ошибка обратитесь к администратору');
	    }

	  }.bind(this)
	});
}

VoitingBulletin.prototype.loadForm = function(e) {
	$.ajax({
	  url: this.url,
	  data: {
	  	user_id: this.choiceMember.val(), 
	  	meeting_id: this.meetingId, 
	  	section_id: this.sectionId, 
	  	answers: [],
	  	action: 'load_form'
	  },
	  success: function(res){

	    console.log(res);
	    if(res.success) {
	    	this.wrapper.html(res.html);
	    } else {
	    	this.wrapper.html('Произошла системная ошибка обратитесь к администратору');
	    }

	  }.bind(this)
	});
}