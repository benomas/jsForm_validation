<form id="test-form">
    <br>
    <br>
    field1:
    <input name="field1" data-formvalidation-name="field1"></input>
    <br>
    field2:
    <select name="field2" data-formvalidation-name="field2">
        <option value="1">
            One
        </option>
        <option value="2">
            Wwo
        </option>
        <option value="3">
            three
        </option>
        <option value="4">
            Four
        </option>
    </select>
    <br>
    <div style="display:block; vertical-align: middle;">
        <div data-formvalidation-name="field3" style="display:inline-block; background-color:#FFFFFF; vertical-align: middle;">
            field3:
        </div>
        <div style="display:inline-block;vertical-align: middle;">
            <input type="radio" name="field3" value="male" >Male<br>
            <input type="radio" name="field3" value="female" >Female<br>
        </div>
    </div>
    field4:
    <textarea name="field4" data-formvalidation-name="field4"></textarea>
    <button id="submit-button" onclick="return false;">Simule</button>
</form>
<script>
/*
    probando mecanismo automatico de pintado de errores form_validation
*/
$(document).ready(function()
{
    var testError = {   "field1":"The <b>Field1</b> should have a validation ",
                        "field2":"The <b>Field2</b> should have a validation ",
                        "field3":"The <b>Field3</b> should have a validation ",
                        "field4":"The <b>Field4</b> should have a validation "
                    };
    var testList = ['field1','field2','field3','field4'];

    var formValidationRenderInstance =  new jsFormValidationRender(document,testList,testError);
    formValidationRenderInstance.makeHtmlJsFormValidationErrors();

    $("#submit-button").click(function()
    {
        $.ajax(
        {
            url : 'testValidations/',
            type: 'POST',
            data: $('#test-form').serialize(),
            dataType: "json",
            success : function(jsonResponse)
            {
                var response = jsonResponse;
                if(response.status==="correct")
                    alert('no errors to show');
                else
                {
                    formValidationRenderInstance.reloadErrors(response.errorList);
                }
            },
            error:function(json)
            {
                alert('ajax fail');
            }
        });
    });
});
</script>
