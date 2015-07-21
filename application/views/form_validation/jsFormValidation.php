<style>
/*class for error message box*/

<?php
    /* customize your ouwn propertys*/
    if(isset($jsFormValidationDinamicError))
        echo '.js-form-validation-dinamic-error{'.$jsFormValidationDinamicError.'} ';
    else
    {
?>

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
<?php
}
?>


<?php
    /* customize your ouwn propertys*/
    if(isset($jsFormValidationErrorContainer))
        echo '.js-form-validation-error-container{'.$jsFormValidationErrorContainer.'} ';
    else
    {
?>
    /*class for wraped element box*/
    .js-form-validation-error-container
    {
        background-color:#C9302C;
        padding:2px;
        display:inline-block;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }
<?php
}
?>

<?php
if(isset($fieldCssConfiguration))
    foreach($fieldCssConfiguration AS $index=>$field)
    {
        if(isset($field['fieldContainer']))
            echo ".js-form-validation-error-container-custom-".$index."{".$field['fieldContainer']."}";
        if(isset($field['errorContainer']))
            echo ".js-form-validation-dinamic-error-custom-".$index."{".$field['errorContainer']."}";
    }
?>
<?php
    /* customize your ouwn propertys*/
    if(isset($jsFormValidationErrorContainer))
        echo '.js-form-validation-error-container{'.$jsFormValidationErrorContainer.'} ';
?>

</style>
<!-- box with list of field errors -->
<div class="js-form-validation-list-errors">
</div>
<script>
    /* json array with all the processed errors*/
var jsFormValidationErrorsObject = [] ;
    /* json object with all the data elements names to follow */
var jsFormValidationFieldList = <?php if(isset($fieldList)){ echo json_encode($fieldList);}else{ echo '[]';}?>;

/* alias iterator for data element name selector*/
var tempField;
/* alias iterator for error message box selector of data element*/
var tempError;
/* alias iterator for data element box container selector*/
var fieldContainer;

var customFieldsConfiguration = <?php if(isset($fieldCssConfiguration)) echo json_encode($fieldCssConfiguration); else echo '[]';?>;
var custom_field_container_clases = "<?php if(isset($custom_field_container_clases)) echo $custom_field_container_clases;?>";
var custom_error_container_clases = "<?php if(isset($custom_error_container_clases)) echo $custom_error_container_clases;?>";



/**
 * descripcion  remove preview validation errors and triggers
 * @author      Benomas (benomas@gmail.com) 2015
 * @return      false
 */
function cleanJsFormValidationErrors()
{
    $.each(jsFormValidationFieldList,function(index,value)
    {
        if($($("[name='"+value+"']").parent()).hasClass("js-form-validation-error-container"))
        {
            $($("[name='"+value+"']").parent()).unbind('mouseenter');
            $($("[name='"+value+"']").parent()).unbind('mouseleave');
            $("[name='"+value+"']").unwrap();

            $("[name='"+value+"']").unbind("showError");
            $("[name='"+value+"']").unbind("hideError");
            $(".js-form-validation-dinamic-error.error-identifier-"+value).remove();
        }
    });
    $(".js-form-validation-list-errors").html('');
    return false;
}


/**
 * descripcion  process a new list of errors
 * @author      Benomas  (benomas@gmail.com) 2015
 * @param       json array of errors {"field":"error field message"}
 * @return      void
 */
function setJsFormValidationErrorsObject(newValues)
{
    cleanJsFormValidationErrors();
    jsFormValidationErrorsObject = newValues;
    makeHtmlJsFormValidationErrors();
    jsFormValidationBinder();
}

/**
 * descripcion  insert html for prepare messages to show
 * @author      Benomas  (benomas@gmail.com) 2015
 * @param
 * @return      void
 */
function makeHtmlJsFormValidationErrors()
{
    $.each(jsFormValidationFieldList,function(index,value)
    {
        if(typeof jsFormValidationErrorsObject[value]!== 'undefined')
        {
            tempField = $("[name='"+value+"']");


            //fieldContainer"=>"","errorContainer"
            if(typeof customFieldsConfiguration[value] !=='undefined' && typeof customFieldsConfiguration[value]["fieldContainer"] !=='undefined' )
                tempField.wrap('<div class="'+custom_field_container_clases + ' js-form-validation-error-container-custom-' +value+ ' js-form-validation-error-container error-identifier-'+value+'"></div>');
            else   
                tempField.wrap('<div class="'+custom_field_container_clases+' js-form-validation-error-container error-identifier-'+value+'"></div>');
            
            if(typeof customFieldsConfiguration[value] !=='undefined' && typeof customFieldsConfiguration[value]["errorContainer"] !=='undefined' )
                $(".js-form-validation-list-errors").append('<div class=" ' + custom_error_container_clases + ' js-form-validation-dinamic-error-custom-' +value+ ' js-form-validation-dinamic-error error-identifier-'+value+'" >'+jsFormValidationErrorsObject[value]+'</div>');
            else                
                $(".js-form-validation-list-errors").append('<div class=" ' + custom_error_container_clases + ' js-form-validation-dinamic-error error-identifier-'+value+'" >'+jsFormValidationErrorsObject[value]+'</div>');

            $.each(tempField,function(index,subValue)
            {
                if($(subValue).prop("type")==='radio')
                {
                    $(subValue).parent().css({"padding-bottom":"4px"});
                }

                $($(subValue).parent()).on( "mouseenter", function()
                {
                    if( typeof $(subValue).prop("pintrot-show-animation") ==="undefined" || $(subValue).prop("pintrot-show-animation")==="false")
                    {
                        $(subValue).prop("pintrot-show-animation","true");
                        $(subValue).trigger("showError");
                    }
                });

                $($(subValue).parent()).on( "mouseleave",function()
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

/**
 * descripcion  bind events for show current validation errors
 * @author      Benomas  (benomas@gmail.com) 2015
 * @param
 * @return      void
 */
function jsFormValidationBinder()
{
    $.each(jsFormValidationFieldList,function(index,value)
    {
        $.each($("[name='"+value+"']"),function(index,subValue)
        {
            if($(subValue).parent().hasClass("js-form-validation-error-container"))
            {
                tempError = $(".js-form-validation-dinamic-error.error-identifier-"+$(subValue).prop("name"));
                tempError.hide();

                $(subValue).bind("showError",function()
                {
                    fieldContainer = $(subValue).parent();
                    tempError       =$(".js-form-validation-dinamic-error.error-identifier-"+$(subValue).prop("name"));

                    tempError.css("opacity","0");

                    tempError.show(0,function()
                    {

                        /*simulate min content width for IE*/
                        tempError.css("-ms-grid-columns","min-content");
                        tempError.css("display","-ms-grid");


                        /*set min content width for other browsers*/
                        tempError.width('-moz-min-content');
                        tempError.width('-webkit-min-content');

                        /*if error message box has more width that field container box adjust top right corner border radius*/
                        if(tempError.outerWidth() > fieldContainer.width())
                        {
                            tempError.css("border-top-right-radius","8px");
                        }
                        else
                        {
                            tempError.css("border-top-right-radius","0");
                        }

                        tempError.css("min-width",tempError.css("width"));
                        tempError.offset({left:0,top:0});
                        tempError.offset({left:fieldContainer.offset().left,top:fieldContainer.offset().top + fieldContainer.outerHeight()});
                        tempError.outerWidth(fieldContainer.outerWidth());

                        tempError.hide(0,function()
                        {
                            tempError.css("opacity","1");
                            $(subValue).prop("pintrot-show-animation","true");
                            $(".js-form-validation-dinamic-error.error-identifier-"+value).slideDown(500);
                        });
                    });


                });
                $(subValue).bind("hideError",function()
                {
                    $(".js-form-validation-dinamic-error.error-identifier-"+value).slideUp(300,function()
                    {
                        $(subValue).prop("pintrot-show-animation","false");
                    });
                });
            }
        });
    });
}
</script>