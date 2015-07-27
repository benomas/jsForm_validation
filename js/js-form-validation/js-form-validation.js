//exted jquery for detect when selector is empty
$.fn.emptyObject = function ()
{
    return this.length === 0;
}

//error object
function jsFormValidationError(jqueryObject)
{
    //jquery selector
    this.jqueryObject           = null;
    //html element's name selector
    this.name                   = '';
    //jquery string selector
    this.selector               = '';
    //jquery element container wraped to the tarjet element
    this.errorContainer         = null;
    //error message html
    this.errorMessage           = '';
    //jquery object from error message html
    this.errorMessageContainer  = null;
    //catching current animation status
    this.animationStatus        = 'waiting';
    //detect if a showAnimation need to run after finish last hide animation
    this.requireShowAnimation   = false;
    //detect if a hideAnimation need to run after finish last show animation
    this.requireHideAnimation   = false;

    //set tarjet element to draw formValidationError
    this.initializeJsFormValidationError = function(jqueryObject)
    {
        this.jqueryObject   = jqueryObject;
        this.name           = this.jqueryObject.prop('name');
        this.selector       = "[name='"+this.jqueryObject.prop('name')+"']";
        this.errorContainer = null;
        this.errorMessage   = '';
        this.errorMessageContainer = null;
    }

    //delete last drawed error
    this.cleanJsFormValidationError = function()
    {
        if(this.errorContainer!== null)
        {
            $(this.errorContainer).unbind('mouseenter');
            $(this.errorContainer).unbind('mouseleave');
            this.jqueryObject.unwrap();
        }
        if(this.errorMessageContainer!== null)
        {
            this.errorMessageContainer.remove();
        }
        this.errorContainer = null;
        this.errorMessage   = '';
        this.errorMessageContainer = null;
    }
    //return current status animation
    this.getAnimationStatus         = function()
    {
        return this.animationStatus;
    }
    //set status animation as showing
    this.showingError               =function()
    {
        this.animationStatus = 'showing';
    }

    //set status animation as hidding
    this.hidingError                =function()
    {
        this.animationStatus = 'hiding';
    }

    //set status animation as waiting
    this.animationErrorComplete                =function()
    {
        this.animationStatus = 'waiting';
    }
    //set error container jquery object
    this.setErrorContainer          = function(html)
    {
        this.jqueryObject.wrap(html);
        this.errorContainer = this.jqueryObject.parent();
    }

    //set error message container jquery object
    this.setErrorMessageContainer   = function(jqueryObject)
    {
        this.errorMessageContainer = jqueryObject;
    }

    //set error message text
    this.setErrorMessage            = function()
    {
        this.errorMessage = this.errorMessageContainer.text();
    }
    //get prop name of current element
    this.getName                    = function()
    {
        return this.name ;
    }
    //bind hide and show animation events
    this.makeError                  = function(Message)
    {
        var thisInvoker = this;
        thisInvoker.errorContainer.bind('mouseenter',function()
        {
            if(thisInvoker.getAnimationStatus()==='waiting')
            {
                thisInvoker.showingError();
                thisInvoker.errorMessageContainer.show(0,function()
                {
                    thisInvoker.errorMessageContainer.css("opacity","0");

                    thisInvoker.errorMessageContainer.css("-ms-grid-columns","min-content");
                    thisInvoker.errorMessageContainer.css("display","-ms-grid");


                    /*set min content width for other browsers*/
                    thisInvoker.errorMessageContainer.width('-moz-min-content');
                    thisInvoker.errorMessageContainer.width('-webkit-min-content');

                    /*if error message box has more width that field container box adjust top right corner border radius*/
                    if(thisInvoker.errorMessageContainer.outerWidth() > thisInvoker.errorContainer.outerWidth())
                    {
                        thisInvoker.errorMessageContainer.css("border-top-right-radius","8px");
                    }
                    else
                    {
                        thisInvoker.errorMessageContainer.css("border-top-right-radius","0");
                    }

                    thisInvoker.errorMessageContainer.css("min-width",thisInvoker.errorMessageContainer.css("width"));
                    thisInvoker.errorMessageContainer.offset({left:0,top:0});
                    thisInvoker.errorMessageContainer.offset({left:thisInvoker.errorContainer.offset().left,top:thisInvoker.errorContainer.offset().top + thisInvoker.errorContainer.outerHeight()});
                    thisInvoker.errorMessageContainer.outerWidth(thisInvoker.errorContainer.outerWidth());

                    thisInvoker.errorMessageContainer.css("opacity","1");
                    thisInvoker.errorMessageContainer.hide(0,function()
                    {
                        thisInvoker.errorMessageContainer.slideDown(500,function()
                        {
                            if(thisInvoker.requireHideAnimation)
                            {
                                thisInvoker.errorMessageContainer.slideUp(500,function()
                                {
                                    thisInvoker.requireHideAnimation =  false;
                                    thisInvoker.animationErrorComplete();
                                });
                            }
                            else
                                thisInvoker.animationErrorComplete();
                        });
                    });
                });
            }
            else
                thisInvoker.requireShowAnimation = true;
        });

        this.errorContainer.bind('mouseleave',function()
        {
            if(thisInvoker.getAnimationStatus()==='waiting')
            {
                thisInvoker.hidingError();
                thisInvoker.errorMessageContainer.slideUp(500,function()
                {
                    thisInvoker.animationErrorComplete();
                });
            }
            else
                thisInvoker.requireHideAnimation = true;
        });

    }

    this.initializeJsFormValidationError(jqueryObject);

}

function jsFormValidationRender(context,fieldList,errorsObject)
{
    this.jsFormValidationErrorListContainer         =null;
    this.jsFormValidationContext                    =context;
    this.jsFormValidationErrorsObject               =[];
    this.jsFormValidationFieldList                  =[];
    this.jsFormValidationFieldListElements          =[];
    this.jsFormValidationDinamicErrorClasses        ='';
    this.jsFormValidationErrorContainerClasses      ='';

    this.jsIsSet                                    =function(variable)
    {
        return typeof variable !== 'undefined';
    }

    this.setJsFormValidationErrorsObject            =function(jsFormValidationErrorsObject)
    {
        if(!this.jsIsSet(jsFormValidationErrorsObject))
            jsFormValidationErrorsObject=[];
        this.jsFormValidationErrorsObject = jsFormValidationErrorsObject;
    }

    this.setJsFormValidationFieldList               =function(jsFormValidationFieldList)
    {
        if(!this.jsIsSet(jsFormValidationFieldList))
            jsFormValidationFieldList=[];
        this.jsFormValidationFieldList  = jsFormValidationFieldList;
    }

    this.setJsFormValidationFieldListElements       =function()
    {
        var thisInvoker = this;
        $.each(this.jsFormValidationFieldList,function(index1,level1)
        {
            $.each($("[name='"+level1+"']",thisInvoker.jsFormValidationContext),function(index2,level2)
            {
                thisInvoker.jsFormValidationFieldListElements.push(new jsFormValidationError($(level2)));
            });
        });
    }

    this.setJsFormValidationDinamicErrorClasses     =function(jsFormValidationDinamicErrorClasses)
    {
        if(!this.jsIsSet(jsFormValidationDinamicErrorClasses))
            jsFormValidationDinamicErrorClasses=' ';
        this.jsFormValidationDinamicErrorClasses = ' js-form-validation-dinamic-error ' +jsFormValidationDinamicErrorClasses;
    }

    this.setJsFormValidationErrorContainerClasses   =function(jsFormValidationErrorContainerClasses)
    {
        if(!this.jsIsSet(jsFormValidationErrorContainerClasses))
            jsFormValidationErrorContainerClasses=' ';
        this.jsFormValidationErrorContainerClasses = ' js-form-validation-error-container ' + jsFormValidationErrorContainerClasses;
    }

    this.setJsFormValidationContext                 =function(context)
    {
        this.context = context;
    }

    this.cleanJsFormValidationErrors                =function ()
    {
        $.each(this.jsFormValidationFieldListElements,function(index,level1)
        {
            level1.cleanJsFormValidationError();
        });
        return false;
    }

    this.makeHtmlJsFormValidationErrors             = function ()
    {
        var thisInvoker = this;
        $.each(thisInvoker.jsFormValidationFieldListElements,function(index1,errorValue)
        {
            if( errorValue.getName() !=='' && thisInvoker.jsIsSet(thisInvoker.jsFormValidationErrorsObject[errorValue.getName()]))
            {
                errorValue.setErrorContainer('<div class="'+thisInvoker.jsFormValidationErrorContainerClasses+' error-identifier-'+errorValue.getName()+'"></div>');
                var newErrorMessage = $('<div class=" ' + thisInvoker.jsFormValidationDinamicErrorClasses + ' error-identifier-'+errorValue.getName()+'" >'+thisInvoker.jsFormValidationErrorsObject[errorValue.getName()]+'</div>');
                $(".js-form-validation-list-errors").append(newErrorMessage);
                newErrorMessage.hide(0,function()
                {
                    errorValue.setErrorMessageContainer(newErrorMessage);
                    errorValue.makeError();
                });
            }
        });
    }

    this.reloadErrors                                =function(errorsObject)
    {
        this.cleanJsFormValidationErrors();
        this.setJsFormValidationErrorsObject(errorsObject);
        this.makeHtmlJsFormValidationErrors();
    }

    this.initializeJsFormValidation                  =function(context,fieldList,errorsObject)
    {
        if($(".js-form-validation-list-errors").emptyObject())
        {
            this.jsFormValidationErrorListContainer =$('<div class="js-form-validation-list-errors"></div>');
            $('body').append(this.jsFormValidationErrorListContainer);
        }
        this.setJsFormValidationErrorContainerClasses();
        this.setJsFormValidationDinamicErrorClasses();
        this.setJsFormValidationContext(context);
        this.setJsFormValidationFieldList(fieldList);
        this.setJsFormValidationFieldListElements();
        this.setJsFormValidationErrorsObject(errorsObject);
    }
    this.initializeJsFormValidation(context,fieldList,errorsObject);
}
//Todo continue commenting code
//Todo add some strategy for append errors when element has not name property