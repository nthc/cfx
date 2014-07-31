/**
 * Main package for the entire WYF javascript file.
 */

var wyf =
{
    getMulti : function(params, callback)
    {
        $.getJSON('/system/api/get_multi?params=' + escape(JSON.stringify(params)),
            function(response){
                if(typeof callback === 'function') callback(response);
            }
        );
    },
    
    openWindow : function(location)
    {
        window.open(location);
    },

    showUploadedData : function(data)
    {
        $("#import-preview").html(data);
    },
    
    updateFilter: function(table, model, value)
    {
        if(value == 0)
        {
            externalConditions[table] = "";
        }
        else
        {
            externalConditions[table] = model + "=" + value;
        }
        window[table + 'Search']();
    },

    confirmRedirect:function(message,path)
    {
        if(confirm(message))
        {
            document.location=path;
        }
    },
	
    init:function()
    {
        wyf.menus.init();
        wyf.tapi.init();
    },

    menus: 
    {
        expand:function(id)
        {
            $("#"+id).slideToggle("fast",
                function()
                {
                    document.cookie = id+"="+$("#"+id).css("display");
                }
                );
        },
		
        init:function()
        {
            raw_cookies = document.cookie.split(";");
            for(var i = 0; i < raw_cookies.length; i++)
            {
                nv_pair = raw_cookies[i].split("=");
                if(nv_pair[0].match("menu-"))
                {
                    nv_pair[0]=nv_pair[0].replace(/^\s+|\s+$/g, '');
                    $("#"+nv_pair[0]).attr("style","display:"+nv_pair[1]);
                }
            }				
        }
    },

    tapi:
    {	
        tables: new Object(),
        tableIds: new Array(),
        activity : null,
		
        addTable: function(id,obj)
        {
            wyf.tapi.tableIds.push(id);
            wyf.tapi.tables[id] = obj;
            wyf.tapi.tables[id].prevPage = 0;
        },
		
        init:function()
        {
            for(var i=0; i < wyf.tapi.tableIds.length; i++)
            {
                var id = wyf.tapi.tableIds[i];  
                //$("#"+id+">tbody").load(wyf.tapi.tables[id].path);
                wyf.tapi.render(wyf.tapi.tables[id]);
            }
        },
		
        render:function(table,action,params)
        {
            var urlParams = "params=" + escape(JSON.stringify(table));
            
            try{
                wyf.tapi.activity.abort();
            }
            catch(e)
            {
                
            }

            wyf.tapi.activity = $.ajax({
                type:"POST",
                url:table.url,
                dataType:"json",
                data:urlParams,
                success:function(r)
                {
                    $("#"+table.id+">tbody").html(r.tbody);
                    $("#"+table.id+"Footer").html(r.footer);
                    $('#'+table.id+"-operations").html(r.operations);
                }
            });
        },
		
        sort:function(id,field)
        {
            if(wyf.tapi.tables[id].sort == "ASC")
            {
                wyf.tapi.tables[id].sort = "DESC";
            }
            else
            {
                wyf.tapi.tables[id].sort = "ASC";
            }
			
            //$("#"+id+">tbody").load(wyf.tapi.tables[id].path+"&sort="+field+"&sort_type="+wyf.tapi.tables[id].sort);
            wyf.tapi.tables[id].sort_field[0].field = field;
            wyf.tapi.tables[id].sort_field[0].type = wyf.tapi.tables[id].sort;
            wyf.tapi.render(wyf.tapi.tables[id]);
        },
		
        switchPage:function(id,page)
        {
            var table = wyf.tapi.tables[id]; 
            table.page = page;
            wyf.tapi.render(table);
            $("#"+id+"-page-id-"+page).addClass("page-selected");
            $("#"+id+"-page-id-"+table.prevPage).removeClass("page-selected");
            table.prevPage = page;
        },
		
        showSearchArea:function(id)
        {
            $("#tapi-"+id+"-search").toggle();
        },
		
        checkToggle:function(id,checkbox)
        {
            $("."+id+"-checkbox").attr("checked", checkbox.checked);
        },
		
        remove:function(id)
        {
            var ids = new Array();
            if(confirm("Are you sure you want to delete the selected elements?"))
            {
                $("."+id+"-checkbox").each(
                    function()
                    {
                        if(this.checked)
                        {
                            ids.push(this.value);
                        }
                    }
                    );
                wyf.tapi.render(wyf.tapi.tables[id],"delete",JSON.stringify(ids));
            }
        },
		
        showOperations:function(tableId, id)
        {
            var offset = $('#'+tableId+'-operations-row-' + id).offset();
            var tableOffset = $('#' + tableId).offset();
            $(".operations-box").hide();
            
            $("#"+tableId+"-operations-box-" + id).css(
                {
                    left:((tableOffset.left) + $('#' + tableId).width() - ($("#"+tableId+"-operations-box-" + id).width() + 65))+'px',
                    top: (offset.top + 1) + 'px'
                }
            ).show();
			
        }
    }
};

function expand(id)
{
    $("#"+id).slideToggle("fast",
        function()
        {
            document.cookie = id+"="+$("#"+id).css("display");
            if(typeof menuExpanded === 'function')
            {
                menuExpanded();
            }
        }
    );
}
