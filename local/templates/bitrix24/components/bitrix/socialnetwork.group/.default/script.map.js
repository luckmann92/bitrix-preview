{"version":3,"sources":["script.js"],"names":["BX","namespace","window","B24SGControl","this","instance","groupId","groupOpened","waitPopup","waitTimeout","notifyHintPopup","notifyHintTimeout","notifyHintTime","favoritesValue","newValue","getInstance","prototype","init","params","parseInt","bind","delegate","sendJoinRequest","addCustomEvent","event","getEventId","eventData","getData","code","data","value","setNotifyButton","slider","top","SidePanel","Instance","getSliderByWindow","util","in_array","SocialnetworkUICommon","reloadBlock","blockId","setSubscribe","_this","showWait","action","hasClass","ajax","url","method","dataType","groupID","sessid","bitrix_sessid","onsuccess","processSubscribeAJAXResponse","PreventDefault","hideError","showButtonWait","getAttribute","MESSAGE","ajax_request","save","responseData","hideButtonWait","URL","addClass","onCustomEvent","location","href","ERROR_MESSAGE","length","showError","onfailure","message","setFavorites","lang","processFavoritesAJAXResponse","NAME","id","name","extranet","EXTRANET","showHint","button","showNotifyHint","adjust","attrs","title","removeClass","SUCCESS","closeWait","RESULT","type","isNotEmptyString","ERROR","processAJAXError","errorCode","indexOf","showErrorPopup","timeout","setTimeout","PopupWindow","autoHide","lightShadow","zIndex","content","create","props","className","children","html","setBindElement","show","clearTimeout","close","el","hint_text","style","display","closeByEsc","closeIcon","offsetLeft","offsetTop","TEXT","innerHTML","setAngle","errorText","errorPopup","Math","random","SGMSetSubscribe"],"mappings":"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","file":""}