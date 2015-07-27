<style>
/*class for error message box*/
    .js-form-validation-dinamic-error
    {
        background-color:#F2DEDE;
        color:#a94442;
        position: absolute;
        padding:5px 10px;
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
        border:2px solid #C9302C;
    }

    /*class for wraped element box*/
    .js-form-validation-error-container
    {
        background-color:#C9302C;
        padding:2px;
        display:inline-block;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }
</style>
<script>

$.fn.emptyObject = function ()
{
    return this.length === 0;
}

function jsFormValidationError(jqueryObject)
{
    this.jqueryObject           = null;
    this.name                   = '';
    this.selector               = '';
    this.errorContainer         = null;
    this.errorMessage           = '';
    this.errorMessageContainer  = null;
    this.animationStatus        = 'waiting';
    this.requireShowAnimation   = false;
    this.requireHideAnimation   = false;

    this.initializeJsFormValidationError = function(jqueryObject)
    {
        this.jqueryObject   = jqueryObject;
        this.name           = this.jqueryObject.prop('name');
        this.selector       = "[name='"+this.jqueryObject.prop('name')+"']";
        this.errorContainer = null;
        this.errorMessage   = '';
        this.errorMessageContainer = null;
    }

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

    this.getAnimationStatus         = function()
    {
        return this.animationStatus;
    }

    this.showingError               =function()
    {
        this.animationStatus = 'showing';
    }

    this.hidingError                =function()
    {
        this.animationStatus = 'hiding';
    }


    this.animationErrorComplete                =function()
    {
        this.animationStatus = 'waiting';
    }

    this.setErrorContainer          = function(html)
    {
        this.jqueryObject.wrap(html);
        this.errorContainer = this.jqueryObject.parent();
    }

    this.setErrorMessageContainer   = function(jqueryObject)
    {
        this.errorMessageContainer = jqueryObject;
    }

    this.setErrorMessage            = function()
    {
        this.errorMessage = this.errorMessageContainer.text();
    }

    this.getName                    = function()
    {
        return this.name ;
    }

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
</script>