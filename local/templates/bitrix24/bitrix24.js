
/*Global Settings */
(function() {

	var iframeMode = window !== window.top;
	var search = window.location.search;
	var sliderMode = search.indexOf("IFRAME=") !== -1 || search.indexOf("IFRAME%3D") !== -1;

	if (iframeMode && sliderMode)
	{
		return;
	}
	else if (iframeMode)
	{
		window.top.location = window.location.href;
		return;
	}

	BX.Bitrix24.PageSlider.bindAnchors({
		rules: [
			{
				condition: [
					'/company/personal/user/(\\d+)/tasks/task/view/(\\d+)/',
					'/workgroups/group/(\\d+)/tasks/task/view/(\\d+)/',
					'/extranet/contacts/personal/user/(\\d+)/tasks/task/view/(\\d+)/'
				],
				loader: 'task-view-loader',
				stopParameters: [
					'PAGEN_(\\d+)',
					'MID'
				]
			},
			{
				condition: [
					'/company/personal/user/(\\d+)/tasks/task/edit/0/',
					'/workgroups/group/(\\d+)/tasks/task/edit/0/',
					'/extranet/contacts/personal/user/(\\d+)/tasks/task/edit/0/'
				],
				loader: 'task-new-loader'
			},
			{
				condition: [
					'/company/personal/user/(\\d+)/tasks/task/edit/(\\d+)/',
					'/workgroups/group/(\\d+)/tasks/task/edit/(\\d+)/',
					'/extranet/contacts/personal/user/(\\d+)/tasks/task/edit/(\\d+)/'
				],
				loader: 'task-edit-loader'
			},
			{
				condition: ['/crm/button/edit/(\\d+)/'],
				loader: 'crm-button-view-loader'
			},
			{
				condition: ['/crm/webform/edit/(\\d+)/'],
				loader: 'crm-webform-view-loader'
			},
			{
				condition: [
					/\/online\/\?(IM_DIALOG|IM_HISTORY)=([a-zA-Z0-9_|]+)/i
				],
				handler: function(event, link)
				{
					if (!window.BXIM)
					{
						return;
					}

					var type = link.matches[1];
					var id = link.matches[2];

					if (type === "IM_HISTORY")
					{
						BXIM.openHistory(id);
					}
					else
					{
						BXIM.openMessenger(id);
					}

					event.preventDefault();
				}
			},
			{
				condition: [
					/^(http|https):\/\/helpdesk\.bitrix24\.([a-zA-Z]{2,3})\/open\/([a-zA-Z0-9_|]+)/i
				],
				allowCrossDomain: true,
				handler: function(event, link)
				{
					var articleId = link.matches[3];
					BX.Helper.show("redirect=detail&HD_ID="+articleId);
					event.preventDefault();
				}
			},
			{
				condition: [ new RegExp("/crm/order/details/[0-9]+/", "i") ],
				loader: "crm-entity-details-loader"			
			},			
			{
				condition: [ new RegExp("/crm/lead/details/[0-9]+/", "i") ],
				loader: "crm-entity-details-loader",
				options: { cacheable: false }
			},
			{
				condition: [ new RegExp("/crm/contact/details/[0-9]+/", "i") ],
				loader: "crm-entity-details-loader",
				options: { cacheable: false }
			},
			{
				condition: [ new RegExp("/crm/company/details/[0-9]+/", "i") ],
				loader: "crm-entity-details-loader"
			},
			{
				condition: [ new RegExp("/crm/deal/details/[0-9]+/", "i") ],
				loader: "crm-entity-details-loader",
				options: { cacheable: false }
			},
			{
				condition: [
					new RegExp("/report/analytics"),
					new RegExp("/report/analytics/\\?analyticBoardKey=(\\w+)")
				],
				options: {
					cacheable: false,
					customLeftBoundary: 0,
					loader: "report:analytics"
				}
			}
		]
	});

	BX.addCustomEvent("onFrameDataRequestFail", function(response) {
		top.location = "/auth/?backurl=" + encodeURIComponent(B24.getBackUrl());
	});

	BX.addCustomEvent("onAjaxFailure", function(status) {
		var redirectUrl = "/auth/?backurl=" + B24.getBackUrl();
		if (status == "auth" && typeof(window.frameRequestStart) !== "undefined")
		{
			top.location = redirectUrl;
		}
	});

	BX.addCustomEvent("onPopupWindowInit", function(uniquePopupId, bindElement, params) {
		//if (BX.util.in_array(uniquePopupId, ["task-legend-popup"]))
		//	params.lightShadow = true;

		if (uniquePopupId == "bx_log_filter_popup")
		{
			params.lightShadow = true;
			params.className = "";
		}
		else if (uniquePopupId == "task-legend-popup")
		{
			params.lightShadow = true;
			params.offsetTop = -15;
			params.offsetLeft = -670;
			params.angle = {offset : 740};
		}
		else if ((uniquePopupId == "task-gantt-filter") || (uniquePopupId == "task-list-filter"))
		{
			params.lightShadow = true;
			params.className = "";
		}
		else if (uniquePopupId.indexOf("sonet_iframe_popup_") > -1)
		{
			params.lightShadow = true;
		}
	});

	BX.addCustomEvent("onJCClockInit", function(config) {

		JCClock.setOptions({
			"centerXInline" : 83,
			"centerX" : 83,
			"centerYInline" : 67,
			"centerY" : 79,
			"minuteLength" : 31,
			"hourLength" : 26,
			"popupHeight" : 229,
			"inaccuracy" : 15,
			"cancelCheckClick" : true
		});
	});

	/*BX.PopupWindow.setOptions({
		"angleMinTop" : 35,
		"angleMinRight" : 10,
		"angleMinBottom" : 35,
		"angleMinLeft" : 10,
		"angleTopOffset" : 5,
		"angleLeftOffset" : 45,
		"offsetLeft" : 0 //-15,
		"offsetTop" : 2,
		"positionTopXOffset" : -11 //20
	});*/

	BX.addCustomEvent("onPullEvent-main", function(command,params){
		if (command == "user_counter" && params[BX.message("SITE_ID")])
		{
			var counters = BX.clone(params[BX.message('SITE_ID')]);
			B24.updateCounters(counters);
		}
	});

	BX.addCustomEvent(window, "onImUpdateCounter", function(counters){

		if (!counters)
			return;

		B24.updateCounters(BX.clone(counters));
	});

	BX.addCustomEvent("onCounterDecrement", function(iDecrement) {

		var counterWrap = BX("menu-counter-live-feed", true);
		if (!counterWrap)
			return;

		iDecrement = parseInt(iDecrement);
		var oldVal = parseInt(counterWrap.innerHTML);
		var newVal = oldVal - iDecrement;
		if (newVal > 0)
			counterWrap.innerHTML = newVal;
		else
			BX.removeClass(counterWrap.parentNode.parentNode.parentNode, "menu-item-with-index");
	});

	BX.addCustomEvent("onImUpdateCounterNotify", function(counter) {
		B24.updateInformer(BX("im-informer-events", true), counter);
	});

	BX.addCustomEvent("onImUpdateCounterMessage", function(counter) {
		B24.updateInformer(BX("im-informer-messages", true), counter);
		B24.updateCounters({'im-message': counter});
	});

	BX.addCustomEvent("onImUpdateCounterNetwork", function(counter) {
		B24.updateInformer(BX("b24network-informer-events", true), counter);
	});

	BX.addCustomEvent("Kanban.Grid:onFixedModeStart", function() {
		BX.ready(function() {
			BX("footer").style.visibility = "hidden";
		});
	});

//connection status===
	BX.addCustomEvent("onPullError", BX.delegate(function(error, code) {
		if (error == 'AUTHORIZE_ERROR')
		{
			B24.connectionStatus("offline");
		}
		else if (error == 'RECONNECT' && (code == 1008 || code == 1006))
		{
			B24.connectionStatus("connecting");
		}
	}, this));

	BX.addCustomEvent("onImError", BX.delegate(function(error, sendErrorCode) {
		if (error == 'AUTHORIZE_ERROR' || error == 'SEND_ERROR' && sendErrorCode == 'AUTHORIZE_ERROR')
		{
			B24.connectionStatus("offline");
		}
		else if (error == 'CONNECT_ERROR')
		{
			B24.connectionStatus("offline");
		}
	}, this));

	BX.addCustomEvent("onPullStatus", BX.delegate(function(status){
		if (status == 'offline')
			B24.connectionStatus("offline");
		else
			B24.connectionStatus("online");
	}, this));

	BX.bind(window, "online", BX.delegate(function(){
		B24.connectionStatus("online");
	}, this));

	BX.bind(window, "offline", BX.delegate(function(){
		B24.connectionStatus("offline");
	}, this));
//==connection status

	if (BX.browser.SupportLocalStorage())
	{
		BX.addCustomEvent(window, 'onLocalStorageSet', function(params)
		{
			if (params.key.substring(0, 4) == 'lmc-')
			{
				var counters = {};
					counters[params.key.substring(4)] = params.value;
				B24.updateCounters(counters, false);
			}
		});
	}

	if (BX.getClass("BX.rest.AppLayout"))
	{
		var placementInterface = BX.rest.AppLayout.initializePlacement("DEFAULT");
		placementInterface.prototype.showHelper = function(params, cb)
		{
			var query = "";
			if (BX.type.isNumber(params))
			{
				query = "redirect=detail&code=" + params;
			}
			else if (BX.type.isNotEmptyString(params))
			{
				query = params;
			}
			else if (BX.type.isPlainObject(params))
			{
				for (var param in params)
				{
					if (query.length)
					{
						query += "&";
					}

					query += param + "=" + params[param];
				}
			}

			if (query.length)
			{
				BX.Helper.show(query);
			}
		};
	}

	BX.ready(function () {
		BX.bind(window, "scroll", BX.throttle(B24.onScroll, 150, B24));
	});
})();

var B24 = {

	b24ConnectionStatusState: "online",
	b24ConnectionStatus: null,
	b24ConnectionStatusText: null,
	b24ConnectionStatusTimeout: null,

	formateDate : function(time){
		return BX.util.str_pad(time.getHours(), 2, '0', 'left') + ':' + BX.util.str_pad(time.getMinutes(), 2, '0', 'left');
	},

	openLanguagePopup: function(button, langs)
	{
		var items = [];
		for (var lang in langs)
		{
			(function(lang) {
				items.push({
					text: langs[lang].NAME + (langs[lang].IS_BETA ? ", beta" : ""),
					onclick: function()
					{
						B24.changeLanguage(lang);
					}
				});
			})(lang);

		}

		BX.PopupMenu.show("language-popup", button, items, { offsetTop:10, offsetLeft:0 });
	},

	changeLanguage: function(lang)
	{
		window.location.href = "/auth/?user_lang=" + lang + "&backurl=" + B24.getBackUrl();
	},

	getBackUrl: function()
	{
		var backUrl = window.location.pathname;
		var query = B24.getQueryString(["logout", "login", "back_url_pub", "user_lang"]);
		return backUrl + (query.length > 0 ? "?" + query : "");
	},

	getQueryString : function(ignoredParams)
	{
		var query = window.location.search.substring(1);
		if (!BX.type.isNotEmptyString(query))
		{
			return "";
		}

		var vars = query.split("&");
		ignoredParams = BX.type.isArray(ignoredParams) ? ignoredParams : [];

		var result = "";
		for (var i = 0; i < vars.length; i++)
		{
			var pair = vars[i].split("=");
			var equal = vars[i].indexOf("=");
			var key = pair[0];
			var value = BX.type.isNotEmptyString(pair[1]) ? pair[1] : false;
			if (!BX.util.in_array(key, ignoredParams))
			{
				if (result !== "")
				{
					result += "&";
				}
				result += key + (equal !== -1 ? "=" : "") + (value !== false ? value : "" );
			}
		}

		return result;
	},

	updateInformer : function(informer, counter)
	{
		if (!informer)
			return false;

		if (counter > 0)
		{
			informer.innerHTML = counter;
			BX.addClass(informer, "header-informer-act");
		}
		else
		{
			informer.innerHTML = "";
			BX.removeClass(informer, "header-informer-act");
		}
	},

	updateCounters : function(counters, send)
	{
		BX.ready(function ()
		{
			if (BX.getClass("BX.Bitrix24.LeftMenuClass"))
			{
				BX.Bitrix24.LeftMenuClass.updateCounters(counters, send);
			}
		});
	},

	showNotifyPopup : function(button)
	{
		if (BX.hasClass(button, "header-informer-press"))
		{
			BX.removeClass(button, "header-informer-press");
			BXIM.closeNotify();
		}
		else
		{
			BXIM.openNotify();
		}
	},

	showMessagePopup : function(button)
	{
		if (typeof(BXIM) == 'undefined')
			return false;

		BXIM.toggleMessenger();
	},

	closeBanner : function(bannerId)
	{
		BX.userOptions.save('bitrix24', 'banners',  bannerId, 'Y');
		var banner = BX("sidebar-banner-" + bannerId);
		if (banner)
		{
			banner.style.minHeight = "auto";
			banner.style.overflow = "hidden";
			banner.style.border = "none";
			(new BX.easing({
				duration : 500,
				start : { height : banner.offsetHeight, opacity : 100 },
				finish : { height : 0, opacity: 0 },
				transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
				step : function(state){
					if (state.height >= 0)
					{
						banner.style.height = state.height + "px";
						banner.style.opacity = state.opacity/100;
					}

					if (state.height <= 17)
					{
						banner.style.marginBottom = state.height + "px";
					}
				},
				complete : function() {
					banner.style.display = "none";
				}
			})).animate();
		}
	},

	showLoading: function(timeout)
	{
		timeout = timeout || 500;
		function show()
		{
			var loader = BX("b24-loader");
			if (loader)
			{
				BX.addClass(loader, "b24-loader-show intranet-loader-show");
				return true;
			}

			return false;
		}

		setTimeout(function() {
			if (!show() && !BX.isReady)
			{
				BX.ready(show);
			}
		}, timeout);
	}
};

/***************** UP button **********************/
B24.onScroll = function()
{
	var windowScroll = BX.GetWindowScrollPos();
	if (B24.b24ConnectionStatus)
	{
		if (B24.b24ConnectionStatus.getAttribute('data-float') == 'true')
		{
			if (windowScroll.scrollTop < 60)
			{
				BX.removeClass(B24.b24ConnectionStatus, 'bx24-connection-status-float');
				B24.b24ConnectionStatus.setAttribute('data-float', 'false');
			}
		}
		else
		{
			if (windowScroll.scrollTop > 60)
			{
				BX.addClass(B24.b24ConnectionStatus, 'bx24-connection-status-float');
				B24.b24ConnectionStatus.setAttribute('data-float', 'true');
			}
		}
	}

	var menu = BX("menu-favorites-settings-btn", true);
	if (!menu)
	{
		return;
	}

	var boundary = menu.offsetHeight ? BX.pos(menu).bottom : BX.GetWindowInnerSize().innerHeight / 2;

	var upBtn = BX("feed-up-btn-wrap", true);
	upBtn.style.left = "-" + windowScroll.scrollLeft + "px";

	if (windowScroll.scrollTop > boundary)
	{
		B24.showUpButton(true, upBtn);
	}
	else
	{
		B24.showUpButton(false, upBtn);
	}
};

B24.showUpButton = function(status, upBtn)
{
	if (!upBtn)
		return;

	if (!!status)
		BX.addClass(upBtn, 'feed-up-btn-wrap-anim');
	else
		BX.removeClass(upBtn, 'feed-up-btn-wrap-anim');
};

B24.goUp = function(fn)
{
	var upBtn = BX("feed-up-btn-wrap", true);
	if (upBtn)
	{
		upBtn.style.display = "none";
		BX.removeClass(upBtn, 'feed-up-btn-wrap-anim');
	}

	var windowScroll = BX.GetWindowScrollPos();

	(new BX.easing({
		duration : 500,
		start : { scroll : windowScroll.scrollTop },
		finish : { scroll : 0 },
		transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
		step : function(state){
			window.scrollTo(0, state.scroll);
		},
		complete: function() {
			if (upBtn)
				upBtn.style.display = "block";
			BX.onCustomEvent(window, 'onGoUp');

			if (BX.type.isFunction(fn))
			{
				fn();
			}

		}
	})).animate();
};

/***************** Left Menu ************************/
B24.toggleMenu = function(menuItem, messageShow, messageHide)
{
	var menuBlock = BX.findChild(menuItem.parentNode, {tagName:'ul'}, false, false);

	var menuItems = BX.findChildren(menuBlock, {tagName : "li"}, false);
	if (!menuItems)
		return;

	var toggleText = BX.findChild(menuItem, {className:"menu-toggle-text"}, true, false);
	if (!toggleText)
		return;

	if (BX.hasClass(menuBlock, "menu-items-close"))
	{
		menuBlock.style.height = "0px";
		BX.removeClass(menuBlock, "menu-items-close");
		BX.removeClass(BX.nextSibling(BX.nextSibling(menuItem)), "menu-items-close");
		menuBlock.style.opacity = 0;
		animation(true, menuBlock, menuBlock.scrollHeight);

		toggleText.innerHTML = messageHide;
		BX.userOptions.save("bitrix24", menuItem.id, "hide", "N");
	}
	else
	{
		animation(false, menuBlock, menuBlock.offsetHeight);
		toggleText.innerHTML = messageShow;
		BX.userOptions.save("bitrix24", menuItem.id, "hide", "Y");
	}

	function animation(opening, menuBlock, maxHeight)
	{
		menuBlock.style.overflow = "hidden";
		(new BX.easing({
			duration : 200,
			start : { opacity: opening ? 0 : 100, height: opening ? 0 : maxHeight },
			finish : { opacity: opening ? 100 : 0, height: opening ? maxHeight : 0 },
			transition : BX.easing.transitions.linear,
			step : function(state)
			{
				menuBlock.style.opacity = state.opacity/100;
				menuBlock.style.height = state.height + "px";

			},
			complete : function()
			{
				if (!opening)
				{
					BX.addClass(menuBlock, "menu-items-close");
					BX.addClass(BX.nextSibling(BX.nextSibling(menuItem)), "menu-items-close");
				}
				menuBlock.style.cssText = "";
			}

		})).animate();
	}
};

B24.licenseInfoPopup = {
	licenseButtonText : "",
	trialButtonText : "",
	showFullDemoButton : "N",
	hostName : "",
	ajaxUrl : "",
	licenseUrl : "",
	demoUrl : "",
	featureGroupName : "",
	ajaxActionsUrl : "",
	tfPrice : "",

	init: function(params)
	{
		if (typeof params == "object")
		{
			this.licenseButtonText = params.B24_LICENSE_BUTTON_TEXT || "";
			this.trialButtonText = params.B24_TRIAL_BUTTON_TEXT || "";
			this.showFullDemoButton = (params.IS_FULL_DEMO_EXISTS == "Y") ? "Y" : "N";
			this.hostName = params.HOST_NAME;
			this.ajaxUrl = params.AJAX_URL;
			this.licenseUrl = params.LICENSE_ALL_PATH;
			this.demoUrl = params.LICENSE_DEMO_PATH;
			this.featureGroupName = params.FEATURE_GROUP_NAME || "";
			this.ajaxActionsUrl = params.AJAX_ACTIONS_URL || "";
			this.featureTrialSuccessText = params.B24_FEATURE_TRIAL_SUCCESS_TEXT || "";
			this.tfPrice = params.tfPrice || "";
		}
	},
	show: function(popupId, title, content, showDemoButton)
	{
		if (!popupId)
			return;

		if (showDemoButton !== false)
		showDemoButton = true;

		title = title || "";
		content = content || "";

		if (BX.type.isNotEmptyString(content) && content.indexOf('#TF_PRICE#') !== -1)
		{
			content = content.replace('#TF_PRICE#', this.tfPrice);
		}

		var buttons = [
			new BX.PopupWindowButton({
				text : this.licenseButtonText,
				className : 'popup-window-button-create',
				events : { click : BX.proxy(function()
				{
					BX.ajax.post(
						this.ajaxUrl,
						{
							popupId: popupId,
							action: "tariff",
							host: this.hostName
						},
						BX.proxy(function(){
							top.location.href = this.licenseUrl;
						}, this)
					);
				}, this)}
			})
		];
		if (this.showFullDemoButton == "Y" && showDemoButton)
		{
			buttons.push(new BX.PopupWindowButtonLink({
				text : this.trialButtonText,
				className : 'popup-window-button-link-cancel',
				events : { click : BX.proxy(function()
				{
					BX.ajax.post(
						this.ajaxUrl,
						{
							popupId: popupId,
							action: "demo",
							host: this.hostName
						},
						BX.proxy(function(){
							document.location.href = this.demoUrl;
						}, this)
					);
				}, this)}
			}));
		}
		else if (this.featureGroupName)
		{
			buttons.push(new BX.PopupWindowButtonLink({
				text : this.trialButtonText,
				className : 'popup-window-button-link-cancel',
				events : { click : BX.proxy(function()
				{
					BX.ajax({
							method: 'POST',
						dataType: 'json',
						url: this.ajaxActionsUrl,
						data: {
							action: "setFeatureTrial",
							sessid: BX.bitrix_sessid(),
							featureGroupName: this.featureGroupName
						},
						onsuccess: BX.proxy(function (json) {
								if (json.error)
									var text = json.error;
								else if (json.success)
									text = this.featureTrialSuccessText;

									if (text)
									{
										BX.PopupWindowManager.create('b24InfoPopupFeature', null, {
											content: BX.create("div", {
											html: text,
											attrs: {style: "padding:10px"}
										}),
										closeIcon: true
									}).show();
								}
							}, this)
					});
					BX.ajax.post(
						this.ajaxUrl,
						{
							popupId: popupId,
							action: "demoFeature",
							host: this.hostName
						},
						function(){}
					);
				}, this)}
			}));
		}

		BX.PopupWindowManager.create('b24InfoPopup'+popupId, null, {
			titleBar: title,
			content:
				BX.create("div", {
					props : { className : "hide-features-popup-wrap" },
					children : [
					BX.create("div", {
						props : { className : "hide-features-popup" },
						children : [
							BX.create("div", {
								props : { className : "hide-features-pic" },
								children : [
									BX.create("div", { props : { className : "hide-features-pic-round" } })
								]}),
							BX.type.isDomNode(content) ?
								BX.create("div", {
									props : { className : "hide-features-text" },
									children: [content]
								})
								: BX.create("div", {
									props : { className : "hide-features-text" },
									html: content
							})
						]
					})
				]}),
			closeIcon : true,
			lightShadow : true,
			offsetLeft : 100,
			overlay : true,
			buttons: buttons,
			events : {
				onPopupClose : BX.proxy(function() {
					BX.ajax.post(
							this.ajaxUrl,
							{
								popupId: popupId,
								action: "close",
								host: this.hostName
						},
						function(){}
					);
				}, this)
			}
		}).show();
	}
};

function showPartnerForm(arParams)
{
	BX = window.BX;
	BX.Bitrix24PartnerForm =
	{
		bInit: false,
		popup: null,
		arParams: {}
	};
	BX.Bitrix24PartnerForm.arParams = arParams;
	BX.message(arParams['MESS']);
	BX.Bitrix24PartnerForm.popup = BX.PopupWindowManager.create("BXPartner", null, {
		autoHide: false,
		zIndex: 0,
		offsetLeft: 0,
		offsetTop: 0,
		overlay : true,
		draggable: {restrict:true},
		closeByEsc: true,
		titleBar: BX.message('BX24_PARTNER_TITLE'),
		closeIcon: { right : "12px", top : "10px"},
		buttons: [
			new BX.PopupWindowButtonLink({
				text: BX.message('BX24_CLOSE_BUTTON'),
				className: "popup-window-button-link-cancel",
				events: { click : function()
				{
					this.popupWindow.close();
				}}
			})
		],
		content: '<div style="width:450px;height:230px"></div>',
		events: {
			onAfterPopupShow: function()
			{
				this.setContent('<div style="width:450px;height:230px">'+BX.message('BX24_LOADING')+'</div>');
				BX.ajax.post(
					'/bitrix/tools/b24_site_partner.php',
					{
						lang: BX.message('LANGUAGE_ID'),
						site_id: BX.message('SITE_ID') || '',
						arParams: BX.Bitrix24PartnerForm.arParams
					},
					BX.delegate(function(result)
						{
							this.setContent(result);
						},
						this)
				);
			}
		}
	});

	BX.Bitrix24PartnerForm.popup.show();
}

/****************** Timemanager *********************/
B24.Timemanager = {

	inited : false,

	layout : {
		block : null,
		timer : null,
		info : null,
		event : null,
		tasks : null,
		status : null
	},

	data : null,
	timer : null,
	clock : null,

	formatTime : function(ts, bSec)
	{
		return BX.util.str_pad(parseInt(ts/3600), 2, '0', 'left')+':'+BX.util.str_pad(parseInt(ts%3600/60), 2, '0', 'left')+(!!bSec ? (':'+BX.util.str_pad(ts%60, 2, '0', 'left')) : '');
	},

	formatWorkTime : function(h, m, s)
	{
		return '<span class="tm-popup-notice-time-hours"><span class="tm-popup-notice-time-number">' + h + '</span></span><span class="tm-popup-notice-time-minutes"><span class="tm-popup-notice-time-number">' + BX.util.str_pad(m, 2, '0', 'left') + '</span></span><span class="tm-popup-notice-time-seconds"><span class="tm-popup-notice-time-number">' + BX.util.str_pad(s, 2, '0', 'left') + '</span></span>';
	},

	formatCurrentTime : function(hours, minutes, seconds)
	{
		var mt = "";
		if (BX.isAmPmMode())
		{
			mt = "AM";
			if (hours > 12)
			{
				hours = hours - 12;
				mt = "PM";
			}
			else if (hours == 0)
			{
				hours = 12;
				mt = "AM";
			}
			else if (hours == 12)
			{
				mt = "PM";
			}

			mt = '<span class="time-am-pm">' + mt + '</span>';
		}
		else
			hours = BX.util.str_pad(hours, 2, "0", "left");

		return '<span class="time-hours">' + hours + '</span>' +
			'<span class="time-semicolon">:</span>' +
			'<span class="time-minutes">' + BX.util.str_pad(minutes, 2, "0", "left") + '</span>' +
			mt;
	},

	init : function(reportJson)
	{
		BX.addCustomEvent("onTimeManDataRecieved", BX.proxy(this.onDataRecieved, this));
		BX.addCustomEvent("onTimeManNeedRebuild", BX.proxy(this.onDataRecieved, this));
		BX.addCustomEvent("onPlannerDataRecieved", BX.proxy(this.onPlannerDataRecieved, this));
		BX.addCustomEvent("onPlannerQueryResult", BX.proxy(this.onPlannerQueryResult, this));
		BX.addCustomEvent("onTaskTimerChange", BX.proxy(this.onTaskTimerChange, this));

		BX.timer.registerFormat("worktime_notice_timeman",BX.proxy(this.formatWorkTime, this));
		BX.timer.registerFormat("bitrix24_time",BX.proxy(this.formatCurrentTime, this));

		BX.addCustomEvent(window, "onTimemanInit", BX.proxy(function() {

			this.inited = true;

			this.layout.block = BX("timeman-block");
			this.layout.timer = BX("timeman-timer");
			this.layout.info = BX("timeman-info");
			this.layout.event = BX("timeman-event");
			this.layout.tasks = BX("timeman-tasks");
			this.layout.status = BX("timeman-status");
			this.layout.statusBlock = BX("timeman-status-block");
			this.layout.taskTime = BX("timeman-task-time");
			this.layout.taskTimer = BX("timeman-task-timer");

			window.BXTIMEMAN.ShowFormWeekly(reportJson);

			BX.bind(this.layout.block, "click", BX.proxy(this.onTimemanClick, this));

			BXTIMEMAN.setBindOptions({
				node: this.layout.block,
				mode: "popup",
				popupOptions: {
					angle : { position : "top", offset : 130},
					offsetTop : 10,
					autoHide : true,
					offsetLeft : -60,
					zIndex : -1,
					events : {
						onPopupClose : BX.proxy(function() {
							BX.removeClass(this.layout.block, "timeman-block-active");
						}, this)
					}
				}
			});

			this.redraw();

		}, this));
	},

	onTimemanClick : function()
	{
		BX.addClass(this.layout.block, "timeman-block-active");
		BXTIMEMAN.Open();
	},

	onTaskTimerChange : function(params)
	{
		if (params.action === 'refresh_daemon_event')
		{
			if(!!this.taskTimerSwitch)
			{
				this.layout.taskTime.style.display = '';
				if(this.layout.info.style.display != 'none')
				{
					this.layout.statusBlock.style.display = 'none';
				}
				this.taskTimerSwitch = false;
			}

			var s = '';
			s += this.formatTime(parseInt(params.data.TIMER.RUN_TIME||0) + parseInt(params.data.TASK.TIME_SPENT_IN_LOGS||0), true);

			if(!!params.data.TASK.TIME_ESTIMATE && params.data.TASK.TIME_ESTIMATE > 0)
			{
				s += ' / ' + this.formatTime(parseInt(params.data.TASK.TIME_ESTIMATE));
			}

			this.layout.taskTimer.innerHTML = s;
		}
		else if(params.action === 'start_timer')
		{
			this.taskTimerSwitch = true;
		}
		else if(params.action === 'stop_timer')
		{
			this.layout.taskTime.style.display = 'none';
			this.layout.statusBlock.style.display = '';
		}
	},

	setTimer : function()
	{
		if (this.timer)
		{
			this.timer.setFrom(new Date(this.data.INFO.DATE_START * 1000));
			this.timer.dt = -this.data.INFO.TIME_LEAKS * 1000;
		}
		else
		{
			this.timer = BX.timer(this.layout.timer, {
				from: new Date(this.data.INFO.DATE_START*1000),
				dt: -this.data.INFO.TIME_LEAKS * 1000,
				display: "simple"
			});
		}
	},

	stopTimer : function()
	{
		if (this.timer != null)
		{
			BX.timer.stop(this.timer);
			this.timer = null;
		}
	},

	redraw_planner: function(data)
	{
		if(!!data.TASKS_ENABLED)
		{
			data.TASKS_COUNT = !data.TASKS_COUNT ? 0 : data.TASKS_COUNT;
			this.layout.tasks.innerHTML = data.TASKS_COUNT;
			this.layout.tasks.style.display = data.TASKS_COUNT == 0 ? "none" : "inline-block";
		}

		if(!!data.CALENDAR_ENABLED)
		{
			this.layout.event.innerHTML = data.EVENT_TIME;
			this.layout.event.style.display = data.EVENT_TIME == '' ? 'none' : 'inline-block';
		}

		this.layout.info.style.display =
			(BX.style(this.layout.tasks, "display") == 'none' && BX.style(this.layout.event, "display") == 'none')
				? 'none'
				: 'block';
	},

	redraw : function()
	{
		this.redraw_planner(this.data.PLANNER);

		if (this.data.STATE == "CLOSED" && (this.data.CAN_OPEN == "REOPEN" || !this.data.CAN_OPEN))
			this.layout.status.innerHTML = this.getStatusName("COMPLETED");
		else
			this.layout.status.innerHTML = this.getStatusName(this.data.STATE);

		// if (this.data.STATE == "OPENED")
		// 	this.setTimer();
		// else
		// {
		// 	this.stopTimer();
		// 	var workedTime = (this.data.INFO.DATE_FINISH - this.data.INFO.DATE_START - this.data.INFO.TIME_LEAKS);
		// 	this.layout.timer.innerHTML = BX.timeman.formatTime(workedTime);
		// }
		if (!this.timer)
			this.timer = BX.timer({container: this.layout.timer, display : "bitrix24_time"}); //BX.timer.clock(this.layout.timer);

		var statusClass = "";
		if (this.data.STATE == "CLOSED")
		{
			if (this.data.CAN_OPEN == "REOPEN" || !this.data.CAN_OPEN)
				statusClass = "timeman-completed";
			else
				statusClass = "timeman-start";
		}
		else if (this.data.STATE == "PAUSED")
			statusClass = "timeman-paused";
		else if (this.data.STATE == "EXPIRED")
			statusClass = "timeman-expired";

		BX.removeClass(this.layout.block, "timeman-completed timeman-start timeman-paused timeman-expired");
		BX.addClass(this.layout.block, statusClass);

		if (statusClass == "timeman-start" || statusClass == "timeman-paused")
		{
			this.startAnimation();
		}
		else
		{
			this.endAnimation();
		}
	},

	getStatusName : function(id)
	{
		return BX.message("TM_STATUS_" + id);
	},

	onDataRecieved : function(data)
	{
		data.OPEN_NOW = false;

		this.data = data;

		if (this.inited)
			this.redraw();
	},

	onPlannerQueryResult : function(data, action)
	{
		if (this.inited)
			this.redraw_planner(data);
	},

	onPlannerDataRecieved : function(ob, data)
	{
		if (this.inited)
			this.redraw_planner(data);
	},

	animation : null,
	animationTimeout : 30000,
	blinkAnimation : null,
	blinkLimit : 10,
	blinkTimeout : 750,

	startAnimation : function()
	{
		if (this.animation !== null)
		{
			this.endAnimation();
		}

		this.startBlink();
		this.animation = setInterval(BX.proxy(this.startBlink, this), this.animationTimeout);
	},

	endAnimation : function()
	{
		this.endBlink();

		if (this.animation)
		{
			clearInterval(this.animation);
		}

		this.animation = null;
	},

	startBlink : function()
	{
		if (this.blinkAnimation !== null)
		{
			this.endBlink();
		}

		var counter = 0;
		this.blinkAnimation = setInterval(BX.proxy(function()
		{
			if (++counter >= this.blinkLimit)
			{
				clearInterval(this.blinkAnimation);
				BX.show(BX("timeman-background", true));
			}
			else
			{
				BX.toggle(BX("timeman-background", true));
			}

		}, this), this.blinkTimeout);
	},

	endBlink : function()
	{
		if (this.blinkAnimation)
		{
			clearInterval(this.blinkAnimation);
		}

		BX("timeman-background", true).style.cssText = "";
		this.blinkAnimation = null;
	}
};

/****************** Invite Dialog *******************/
B24.Bitrix24InviteDialog =
{
	bInit: false,
	popup: null,
	arParams: {}
};

B24.Bitrix24InviteDialog.Init = function(arParams)
{
	if(arParams)
		B24.Bitrix24InviteDialog.arParams = arParams;

	if(B24.Bitrix24InviteDialog.bInit)
		return;

	BX.message(arParams['MESS']);

	B24.Bitrix24InviteDialog.bInit = true;

	BX.ready(BX.delegate(function()
	{
		B24.Bitrix24InviteDialog.popup = BX.PopupWindowManager.create("B24InviteDialog", null, {
			autoHide: false,
			zIndex: 0,
			offsetLeft: 0,
			offsetTop: 0,
			overlay:true,
			draggable: {restrict:true},
			closeByEsc: true,
			titleBar: BX.message('BX24_INVITE_TITLE_INVITE'),
			contentColor: "white",
			contentNoPaddings: true,
			closeIcon: { right : "12px", top : "10px"},
			buttons: [
			],
			content: '<div style="width:500px;height:550px; background: url(/bitrix/templates/bitrix24/images/loader.gif) no-repeat center;"></div>',
			events: {
				onAfterPopupShow: function()
				{
					this.setContent('<div style="width:500px;height:550px; background: url(/bitrix/templates/bitrix24/images/loader.gif) no-repeat center;"></div>');
					BX.ajax.post(
						'/bitrix/tools/intranet_invite_dialog.php',
						{
							lang: BX.message('LANGUAGE_ID'),
							site_id: BX.message('SITE_ID') || '',
							arParams: B24.Bitrix24InviteDialog.arParams
						},
						BX.delegate(function(result)
							{
								this.setContent(result);
							},
							this)
					);
				},
				onPopupClose: function()
				{
					BX.InviteDialog.onInviteDialogClose();
				}
			}
		});
	}, this));
};

B24.Bitrix24InviteDialog.ShowForm = function(arParams)
{
	B24.Bitrix24InviteDialog.Init(arParams);
	B24.Bitrix24InviteDialog.popup.params.zIndex = (BX.WindowManager? BX.WindowManager.GetZIndex() : 0);
	B24.Bitrix24InviteDialog.popup.show();
};

B24.Bitrix24InviteDialog.ReInvite = function(reinvite_user_id)
{
	BX.ajax.post(
		'/bitrix/tools/intranet_invite_dialog.php',
		{
			lang: BX.message('LANGUAGE_ID'),
			site_id: BX.message('SITE_ID') || '',
			reinvite: reinvite_user_id,
			sessid: BX.bitrix_sessid()
		},
		BX.delegate(function(result)
			{
			},
			this)
	);
};

B24.connectionStatus = function(status)
{
	if (!(status == 'online' || status == 'connecting' || status == 'offline'))
		return false;

	if (this.b24ConnectionStatusState == status)
		return false;

	this.b24ConnectionStatusState = status;

	var statusClass = '';

	if (status == 'offline')
	{
		b24ConnectionStatusStateText = BX.message('BITRIX24_CS_OFFLINE');
		statusClass = 'bx24-connection-status-offline';
	}
	else if (status == 'connecting')
	{
		b24ConnectionStatusStateText = BX.message('BITRIX24_CS_CONNECTING');
		statusClass = 'bx24-connection-status-connecting';
	}
	else if (status == 'online')
	{
		b24ConnectionStatusStateText = BX.message('BITRIX24_CS_ONLINE');
		statusClass = 'bx24-connection-status-online';
	}

	clearTimeout(this.b24ConnectionStatusTimeout);

	var connectionPopup = document.querySelector('[data-role="b24-connection-status"]');
	if (!connectionPopup)
	{
		var windowScroll = BX.GetWindowScrollPos();
		var isFloat = windowScroll.scrollTop > 60;

		this.b24ConnectionStatus = BX.create("div", {
			attrs : {
				className : "bx24-connection-status "+(this.b24ConnectionStatusState == 'online'? "bx24-connection-status-hide": "bx24-connection-status-show bx24-connection-status-"+this.b24ConnectionStatusState)+(isFloat? " bx24-connection-status-float": ""),
				"data-role" : "b24-connection-status",
				"data-float" : isFloat? "true": "false"
			},
			children : [
				BX.create("div", { props : { className : "bx24-connection-status-wrap" }, children : [
					this.b24ConnectionStatusText = BX.create("span", { props : { className : "bx24-connection-status-text"}, html: b24ConnectionStatusStateText}),
					BX.create("span", { props : { className : "bx24-connection-status-text-reload"}, children : [
						BX.create("span", { props : { className : "bx24-connection-status-text-reload-title"}, html: BX.message('BITRIX24_CS_RELOAD')}),
						BX.create("span", { props : { className : "bx24-connection-status-text-reload-hotkey"}, html: (BX.browser.IsMac()? "&#8984;+R": "Ctrl+R")})
					], events: {
						'click': function(){ location.reload() }
					}})
				]})
			]
		});
	}
	else
	{
		this.b24ConnectionStatus = connectionPopup;
	}

	if (!this.b24ConnectionStatus)
		return false;

	if (status == 'online')
	{
		clearTimeout(this.b24ConnectionStatusTimeout);
		this.b24ConnectionStatusTimeout = setTimeout(BX.delegate(function(){
			BX.removeClass(this.b24ConnectionStatus, "bx24-connection-status-show");
			this.b24ConnectionStatusTimeout = setTimeout(BX.delegate(function(){
				BX.removeClass(this.b24ConnectionStatus, "bx24-connection-status-hide");
			}, this), 1000);
		}, this), 4000);
	}

	this.b24ConnectionStatus.className = "bx24-connection-status bx24-connection-status-show "+statusClass+" "+(this.b24ConnectionStatus.getAttribute('data-float') == 'true'? 'bx24-connection-status-float': '');
	this.b24ConnectionStatusText.innerHTML = b24ConnectionStatusStateText;

	if (!connectionPopup)
	{
		var nextNode = BX.findChild(document.body, {className: "bx-layout-inner-table"}, true, false);
		nextNode.parentNode.insertBefore(this.b24ConnectionStatus, nextNode);
	}

	return true;
};

B24.showPartnerOrderForm = function (params)
{
	if (typeof params !== "object")
		return;

	BX.PopupWindowManager.create("B24PartnerOrderForm", null, {
		autoHide: true,
		zIndex: 0,
		offsetLeft: 0,
		offsetTop: 0,
		overlay: true,
		height: Math.min(document.documentElement.clientHeight - 100, 740),
		width: 500,
		draggable: {restrict:true},
		closeByEsc: true,
		contentColor: "white",
		contentNoPaddings: true,
		closeIcon: { right : "5px", top : "10px"},
		content:
			'<div style="" id="B24PartnerOrderPopup">' +
				'<div class="intranet-loader-container intranet-loader-show" id="B24PartnerOrderLoader">' +
					'<svg class="intranet-loader-circular" viewBox="25 25 50 50"> ' +
						'<circle ' +
							'class="intranet-loader-path" ' +
							'cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10" ' +
						'/> ' +
					'</svg>' +
				'</div>' +
			'</div>',
		events: {
			onPopupFirstShow: function()
			{
				(function(w,d,u,b){w['Bitrix24FormObject']=b;w[b] = w[b] || function(){arguments[0].ref=u;
						(w[b].forms=w[b].forms||[]).push(arguments[0])};
					if(w[b]['forms']) return;
					s=d.createElement('script');r=1*new Date();s.async=1;s.src=u+'?'+r;
					h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
				})(window,document,'https://cp.bitrix.ru/bitrix/js/crm/form_loader.js','b24form');

				b24form({
					"id": params.id ? params.id : "",
					"lang":params.lang ? params.lang : "",
					"sec":params.sec ? params.sec : "",
					"type":"inline_popup",
					"node": BX("B24PartnerOrderPopup"),
					"options": {'borders': false},
					"handlers":{"load":function () {
						BX.remove(BX("B24PartnerOrderLoader"));
					}}
				});
			}
		}
	}).show();
};
