leantime.modals = (function () {

    var setCustomModalCallback = function(callback) {
        if(typeof callback === 'function') {
            window.globalModalCallback = callback;
        }
    }
    var openModal = function () {

        var modalOptions = {
            sizes: {
                minW: 500,
                minH: 200
            },
            resizable: true,
            autoSizable: true,
            callbacks: {
                beforePostSubmit: function () {

                    jQuery(".showDialogOnLoad").show();

                    if(tinymce.editors.length>0) {
                        tinymce.editors.forEach(function(editor) {
                            editor.save();
                            editor.destroy();
                            editor.remove();
                        });
                    }

                },
                beforeShowCont: function () {
                    jQuery(".showDialogOnLoad").show();
                },
                afterShowCont: function () {
                    window.htmx.process('.nyroModalCont');
                    jQuery(".formModal, .modal").nyroModal(modalOptions);
                    tippy('[data-tippy-content]');
                },
                beforeClose: function () {
                    try{
                        history.pushState("", document.title, window.location.pathname + window.location.search);

                    }catch(error){
                        //Code to handle error comes here
                        console.log("Issue pushing history");
                    }

                    if(typeof window.globalModalCallback === 'function') {
                        window.globalModalCallback();
                    }else{
                        location.reload();
                    }
                }
            },
            titleFromIframe: true
        };

        var url = window.location.hash.substring(1);
        if(url.includes("showTicket")
            || url.includes("ideaDialog")
            || url.includes("articleDialog")) {
            //modalOptions.sizes.minW = 1800;
            //modalOptions.sizes.minH = 1800;
        }

        //Ensure we have no trailing slash at the end.
        var baseUrl = leantime.instanceInfo.appUrl.replace(/\/$/, '');

        var urlParts = url.split("/");
        if(urlParts.length>2 && urlParts[1] !== "tab") {

            htmx.ajax('GET', baseUrl+""+url, {target:'#modal-wrapper', swap:'innerHTML'})

            //jQuery.nmManual(baseUrl+""+url, modalOptions);
        }
    }

    var closeModal = function () {
        jQuery.nmTop().close();
    }

    return {
        openModal:openModal,
        setCustomModalCallback:setCustomModalCallback,
        closeModal:closeModal

    };

})();

jQuery(document).ready(function() {
    leantime.modals.openModal();
});

window.addEventListener("hashchange", function () {
    leantime.modals.openModal();
});

window.addEventListener("closeModal", function(evt) {
    leantime.modals.closeModal();
});

