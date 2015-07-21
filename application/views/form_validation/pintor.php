<style>
.dinamic-error
{
    background-color:#FFAAAA;
    color:#1D4B00;
    position: absolute;
    padding:5px 10px;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
    border:2px solid red;

}

<?php
    if(isset($dinamicError))
        echo '.dinamic-error{'.$dinamicError.'} ';
?>

.error-container
{
    background-color:red;
    padding:2px;
    display:inline-block;
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
}

<?php
    if(isset($errorContainer))
        echo '.error-container{'.$errorContainer.'} ';
?>

</style>
<div class="list-errors">
</div>
<script>
/*
    probando mecanismo automatico de pintado de errores form_validation
*/
var errorValidationObject = [] ;
var pintorList = <?php if(isset($fieldList)){ echo json_encode($fieldList);}else{ echo '[]';}?>;

var tempField;
var tempError;
var fieldContainer;
var defaultCalculed;

function propertyConversor(property)
{
    var stringProperty = property;
    var numericValue;
    numericValue = stringProperty.replace(/^(\d+[.]{0,1}\d*)(.*)/, "$1");
    return parseFloat(numericValue);
}

function cleanErrors()
{
    $.each(pintorList,function(index,value)
    {
        if($("[name='"+value+"']").parent().hasClass("error-container"))
        {
            $("[name='"+value+"']").unwrap();
            $(".dinamic-error.error-identifier-"+value).remove();
        }
    });
    return false;
}

function setErrorValidationObject(newValues)
{
    cleanErrors();
    errorValidationObject = newValues;
    showPintorErrors();
    binder();
}

function showPintorErrors()
{
    $.each(pintorList,function(index,value)
    {
        if(typeof errorValidationObject[value]!== 'undefined')
        {
            tempField = $("[name='"+value+"']");
            tempField.wrap('<div class="error-container error-identifier-'+value+'"></div>');
            $(".list-errors").append("<div class=\"dinamic-error error-identifier-"+value+"\" >"+errorValidationObject[value]+"</div>");

            $.each(tempField,function(index,subValue)
            {
                if($(subValue).prop("type")==='radio')
                {
                    $(subValue).parent().css({"padding-bottom":"4px"});
                }

                $(subValue).hover(function()
                {
                    if( typeof $(subValue).prop("pintrot-show-animation") ==="undefined" || $(subValue).prop("pintrot-show-animation")==="false")
                    {
                        $(subValue).prop("pintrot-show-animation","true");
                        $(subValue).trigger("showError");
                    }
                },function()
                {
                    if( typeof $(subValue).prop("pintrot-show-animation") !=="undefined" && $(subValue).prop("pintrot-show-animation")==="true")
                    {
                        $(subValue).trigger("hideError");
                    }
                });
            });

        }
    });
}

function binder()
{
    $.each(pintorList,function(index,value)
    {
        $.each($("[name='"+value+"']"),function(index,subValue)
        {
            if($(subValue).parent().hasClass("error-container"))
            {
                tempError = $(".dinamic-error.error-identifier-"+$(subValue).prop("name"));
                tempError.hide();

                $(subValue).bind("showError",function()
                {
                    fieldContainer = $(subValue).parent();
                    tempError       =$(".dinamic-error.error-identifier-"+$(subValue).prop("name"));


                    tempError.width('auto');
                    tempError.width('-moz-min-content');
                    tempError.width('min-content');
                    tempError.width('-webkit-min-content');

                    if(tempError.width() > fieldContainer.width())
                    {
                        tempError.css("border-top-right-radius","8px");
                    }
                    else
                    {
                        tempError.css("border-top-right-radius","0");
                    }

                    tempError.css("min-width",tempError.css("width"));
                    tempError.offset({left:fieldContainer.offset().left,top:fieldContainer.offset().top + fieldContainer.outerHeight()});
                    tempError.outerWidth(fieldContainer.outerWidth());

                    $(subValue).prop("pintrot-show-animation","true");
                    $(".dinamic-error.error-identifier-"+value).slideDown("slow");
                });
                $(subValue).bind("hideError",function()
                {
                    //console.log($(subValue).prop("pintrot-show-animation"));
                    $(".dinamic-error.error-identifier-"+value).slideUp("slow",function()
                    {
                        tempError.css("opacity","0");
                        tempError.show(100,function()
                        {
                            tempError.offset({left:0,top:0});
                            tempError.hide(1,function()
                            {
                                tempError.css("opacity","1");
                                $(subValue).prop("pintrot-show-animation","false");
                            });
                        });
                    });
                });
            }
            else
            {
                $(subValue).unbind("showError");
                $(subValue).unbind("hideError");
            }
        });
    });
}

</script>