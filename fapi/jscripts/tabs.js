var curtab = 0;

function fapiSwitchTabTo(n)
{
	if(n==curtab) return;
	$("#fapi-tab-"+String(curtab)).toggle();
	$("#fapi-tab-"+String(n)).toggle();
	$("#fapi-tab-top-"+String(n)).removeClass("fapi-tab-unselected");
	$("#fapi-tab-top-"+String(n)).addClass("fapi-tab-selected");
	$("#fapi-tab-top-"+String(curtab)).removeClass("fapi-tab-selected");
	$("#fapi-tab-top-"+String(curtab)).addClass("fapi-tab-unselected");
	curtab=n;
}


