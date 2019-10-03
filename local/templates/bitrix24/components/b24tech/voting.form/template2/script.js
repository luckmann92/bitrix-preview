var VoitingBulletin = function(userId, meetingId, sectionId, mode, selector) {
	this.wrapper = $('#js-voting-'+selector);
	this.btn = this.wrapper.find('.js-submit-button');
	this.btnFinish = this.wrapper.find('.js-finish-voting');
	this.btnAgreement= this.wrapper.find('.js-agreement-voting');
	this.choiceMember = this.wrapper.find('#js-member-'+selector);
	this.messagesWrapper = this.wrapper.find('.js-messages');
	this.questionRow = this.wrapper.find('.js-question-row');
	this.userId = userId;
	this.meetingId = meetingId;
	this.sectionId = sectionId;
	this.url = '/local/components/b24tech/voting.form/ajax-2.php';
	this.mode = mode;

	this.bindEvents();
	console.log(this);
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
	console.log(answers);
	if(answers.length > 0) {
		this.send(answers);
	}
}

VoitingBulletin.prototype.send = function(data) {
	
	var userId = this.userId;
	if(this.mode == 'offline') {
		// userId = parseInt($('#js-member').val());
		userId = data[0].user;
		this.messagesWrapper.html('').hide();
	}

	delete data[0].user;

	console.log(data);
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
			console.log(res);

		    if(res.success) {
		    	if(this.mode == 'offline') {
		    		this.messagesWrapper.html('Ваш ответ успешно принят').show();
		    		this.questionRow.hide();
		    		this.btn.hide();
		    	} else {
		    		this.wrapper.html('Ваш ответ успешно принят');
		    		this.questionRow.hide();
		    		this.btn.hide();
		    	}
		    } else {
		    	if(this.mode == 'offline') {
		    		this.messagesWrapper.html('Произошла системная ошибка обратитесь к администратору');
		    		this.questionRow.hide();
		    		this.btn.hide();
		    	} else {
					this.wrapper.html('Произошла системная ошибка обратитесь к администратору').show();
		    		this.questionRow.hide();
		    		this.btn.hide();
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
			user: $(this).data('user'),
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