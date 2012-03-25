/**
 * Main package for the entire ntentan javascript file.
 */
ntentan = 
{
	confirmRedirect:function(message,path)
	{
		if(confirm(message))
		{
			document.location=path;
		}
	},
	
	init:function()
	{
		ntentan.menus.init();
		ntentan.tapi.init();
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
		
		addTable: function(id,obj)
		{
			ntentan.tapi.tableIds.push(id);
			ntentan.tapi.tables[id] = obj;
			ntentan.tapi.tables[id].prevPage = 0;
		},
		
		init:function()
		{
			for(var i=0; i<ntentan.tapi.tableIds.length; i++)
			{
				var id = ntentan.tapi.tableIds[i];  
				//$("#"+id+">tbody").load(ntentan.tapi.tables[id].path);
				ntentan.tapi.render(ntentan.tapi.tables[id]);
			}
		},
		
		render:function(table,action,params)
		{
		    var urlParams = "params=" + escape(JSON.stringify(table));

			$.ajax({
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
			if(ntentan.tapi.tables[id].sort == "ASC")
			{
				ntentan.tapi.tables[id].sort = "DESC";
			}
			else
			{
				ntentan.tapi.tables[id].sort = "ASC";
			}
			
			//$("#"+id+">tbody").load(ntentan.tapi.tables[id].path+"&sort="+field+"&sort_type="+ntentan.tapi.tables[id].sort);
			ntentan.tapi.tables[id].sort_field[0].field = field;
            ntentan.tapi.tables[id].sort_field[0].type = ntentan.tapi.tables[id].sort;
			ntentan.tapi.render(ntentan.tapi.tables[id]);
		},
		
		switchPage:function(id,page)
		{
			var table = ntentan.tapi.tables[id]; 
			table.page = page;
			ntentan.tapi.render(table);
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
				ntentan.tapi.render(ntentan.tapi.tables[id],"delete",JSON.stringify(ids));
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
}

function expand(id)
{
    $("#"+id).slideToggle("fast",
        function()
        {
            document.cookie = id+"="+$("#"+id).css("display");
        }
    );
}
