var curtab = 0;
var searchFieldConditions = [];
var request = undefined;

/**
 * Switches the current FAPI tab to the one specified.
 * @todo The tab id should be specified to make it easy to differentiate
 * different tabs.
 */
function fapiSwitchTabTo(n)
{
    if (n == curtab)
        return;
    $("#fapi-tab-" + String(curtab)).toggle();
    $("#fapi-tab-" + String(n)).toggle();
    $("#fapi-tab-top-" + String(n)).removeClass("fapi-tab-unselected");
    $("#fapi-tab-top-" + String(n)).addClass("fapi-tab-selected");
    $("#fapi-tab-top-" + String(curtab)).removeClass("fapi-tab-selected");
    $("#fapi-tab-top-" + String(curtab)).addClass("fapi-tab-unselected");
    curtab = n;
}

function fapiShowMessage(id, message, type)
{
    $("#" + id + "-fapi-message").html("<div class='fapi-" + type + "'><ul><li>" + message + "</li></ul></div>").fadeIn("slow");
    $("#" + id).addClass("fapi-" + type);
}

function fapiHideMessage(id)
{
    $("#" + id + "-fapi-message").fadeOut("slow");
    $("#" + id).removeClass("fapi-error");
}

function fapiCheckRequired(id, validator)
{
    var ret = true;
    if ($("#" + id).val() == "")
    {
        fapiShowMessage(id, validator.message, "error");
        ret = false;
    }
    else
    {
        fapiHideMessage(id);
    }
    return ret;
}

function fapiCheckRegexp(id, validator)
{
    var ret = true;
    if ($("#" + id).val().match(eval(validator.regexp)) == null && $("#" + id).val() != "")
    {
        fapiShowMessage(id, validator.message, "error");
        ret = false;
    }
    else
    {
        fapiHideMessage(id);
    }
    return ret;
}

function fapiCheckUnique(id, validator)
{
    var value = $("#" + id).val();
    if (value == "")
        return;
    fapiShowMessage(id, "Checking availability ...", "info");
    $.ajax
            ({
                type: "GET",
                url: validator.url,
                data: validator.params + "&v=" + value,
                dataType: "json",
                success:
                        function(response)
                        {
                            if (response.status)
                            {
                                fapiShowMessage(id, value + " is available", "info");
                                setTimeout("fapiHideMessage('" + id + "')", 5000);
                            }
                            else
                            {
                                fapiShowMessage(id, value + " is not available. Please try another value.", "error");
                            }
                        }
            });
    return true;
}

function fapiValidate(id, validations)
{
    var ret = true;
    for (var i = 0; i < validations.length; i++)
    {
        if (!validations[i].func(id, validations[i]))
        {
            ret = false;
            break;
        }
    }
    return ret;
}

function fapiMultiFormReset(id)
{
    $('#multiform-contents-' + id).html('');
}

function fapiMultiFormRemove(id)
{
    $("#multiform-content-" + id).remove();
}

function fapiMultiFormAdd(id)
{
    var numMultiForms = parseInt($("#multiform-numitems-" + id).val()) + 1;
    $("#multiform-numitems-" + id).val(numMultiForms);
    var form = $("#multiform-template-" + id).html()
    $("#multiform-contents-" + id).append(form.replace(/--index--/g, numMultiForms));
}

function remove(quem)
{
    if (quem !== null)
        quem.parentNode.removeChild(quem);
}

function addEvent(obj, evType, fn)
{
    // elcio.com.br/crossbrowser
    if (obj.addEventListener)
    {
        obj.addEventListener(evType, fn, true);
    }
    if (obj.attachEvent)
    {
        obj.attachEvent("on" + evType, fn);
    }
}

function removeEvent(obj, type, fn)
{
    if (obj.detachEvent)
    {
        obj.detachEvent('on' + type, fn);
    }
    else
    {
        obj.removeEventListener(type, fn, false);
    }
}

function fapiStartUpload(field, form, script, func, showFieldAfterUpload)
{
    if (showFieldAfterUpload != 1)
    {
        field.style.display = "none";
    }
    $("#" + field.id + "_desc").prepend("<div id='" + field.id + "_upload_notify'>Uploading ...</br><img src='/images/ajax-loader.gif' /></div>");

    var iframe = document.createElement("iframe");
    var tempId = field.id + "_temp";

    iframe.setAttribute("id", tempId);
    iframe.setAttribute("name", tempId);
    iframe.setAttribute("width", "0");
    iframe.setAttribute("height", "0");
    iframe.setAttribute("border", "0");
    iframe.setAttribute("style", "width: 0; height: 0; border: none;");

    form.parentNode.appendChild(iframe);
    window.frames[tempId].name = tempId;

    var callback =
        function()
        {
            removeEvent(document.getElementById(tempId), "load", callback);
            callback_data = document.getElementById(tempId).contentDocument.body.innerHTML;
            remove(document.getElementById(tempId));
            remove(document.getElementById(field.id + "_upload_notify"));
            eval(func);
        };
    addEvent(document.getElementById(tempId), "load", callback);

    //properties of form
    form.setAttribute("target", tempId);
    form.setAttribute("action", script);
    form.setAttribute("method", "post");
    form.setAttribute("enctype", "multipart/form-data");
    form.setAttribute("encoding", "multipart/form-data");

    //submit
    form.submit();
}

function fapiUpdateSearchField(name, url, fields, element, boldFirst, onChangeFunction)
{
    var conditions = '';
    if (element.value == '')
    {
        $("#" + name + "_search_area").html(content).hide('fast');
        return;
    }

    fields = JSON.parse(unescape(fields));

    for (var i = 0; i < fields.length; i++)
    {
        conditions += fields[i] + "=" + element.value + ",";
    }

    try {
        request.abort();
    }
    catch (e)
    {

    }

    request = $.ajax({
        type: "GET",
        url: url + "&conditions=" + escape(conditions) + "&conditions_opr=OR" +
                (searchFieldConditions[name] !== undefined ? "&and_conditions=" + escape(searchFieldConditions[name]) : ''),
        dataType: "json",
        success: function(r)
        {
            var content = "<table class='fapi-select-table'>";
            var data;
            for (var i = 0; i < r.length; i++)
            {
                var value = boldFirst ? r[i][fields[1]] + ' - ' : '';
                for (var j = (boldFirst ? 2 : 1); j < fields.length; j++)
                {
                    data = r[i][fields[j]];
                    value += (data === null ? "" : data) + " ";
                }
                content += "<tr id='" + name + "_search_entry_" + i + "' onclick='fapiSetSearchValue(\"" + name + "\",\"" + r[i][fields[0]] + "\",\"" + value.replace(/^\s+|\s+$/g, "").replace(/'/, "") + "\",\"" + onChangeFunction + "\")'><td>";
                for (j = 1; j < fields.length; j++)
                {
                    data = r[i][fields[j]];
                    content += (j == 1 && boldFirst === true ? "<b>" : "") + (data === null ? "" : data) + (j == 1 ? "</b>" : "") + " ";
                }
                content += "</td></tr>";
            }
            content += "</table>";
            window[name + '_table_position'] = -1;
            if (r.length > 0)
            {
                $("#" + name + "_search_area").html(content).show('fast');

                $('body').click(
                        function()
                        {
                            $("#" + name + "_search_area").html(content).hide('fast');
                        }
                );
            }
            else
            {
                $("#" + name + "_search_area").html(content).hide('fast');
            }
        }
    });
}

function fapiSetSearchValue(name, value, display, func)
{
    $("#" + name + "_search_entry").val(display);
    $("#" + name + "_search_area").hide("fast");
    $("#" + name).val(value);
    window[func](value);
}

function fapiFieldsetCollapse(id)
{
    $("#" + id +"_collapse").slideToggle();
}
