var dtable;

function initFinder(element) {
	element.dialog({
		width: 480,
		open: handleFindDialogOpen,
		autoOpen: false,
		buttons: {
			'Open Story': function() {
				$(this).dialog('close');
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});
}

function getSelectedRows()
{
	var ret = [];
	var aTrs = dtable.fnGetNodes();
	for(var i=0; i<aTrs.length; i++) {
		if($(aTrs[i]).hasClass('row_selected')) {
			ret.push(aTrs[i]);
		}
	}
	return ret;
}

function handleFindDialogOpen(event, ui) {

	if(!dtable)
	{
		$("#find_results tbody").click(function(event) {
			$(dtable.fnSettings().aoData).each(function() {
				$(this.nTr).removeClass('row_selected');
			});
			$(event.target.parentNode).addClass('row_selected');
		});

		dtable = $("#find_results").dataTable({
			bFilter:false, 
			bPaginate:false, 
			bInfo:false,
			aoColumns: [null, null, { bVisible: false }],
		});	
	}

	$("#find_form :text").keyup(handleFindDialogKeyPress);
	$("#find_form :text").focus(handleFindDialogFocus);
	$("#find_form :text").blur(handleFindDialogBlur);
}

function handleFindDialogBlur(event) {
	var el = $(event.target);
	if(el.val() == '')
		el.val(el.attr('title')).addClass('default_text');
}

function handleFindDialogFocus(event) {
	var el = $(event.target);
	if(el.hasClass('default_text'))
		el.val('').removeClass('default_text');
}

function handleFindDialogKeyPress(event) {
	var uriVal = $("#find_uri").val();
	var keyVal = $("#find_keywords").val();
	uriVal = ($("#find_uri").hasClass('default_text') ? "" : uriVal);
	keyVal = ($("#find_keywords").hasClass('default_text') ? "" : keyVal);

	findObjects(uriVal, keyVal, refreshFindDialog);
}

function refreshFindDialog(json) {
	// Clear current rows
	dtable.fnClearTable();
	// Repopulate
	var bindings = json.results.bindings;
	for(var j=0; j<bindings.length; j++)
	{
		dtable.fnAddData([bindings[j].label.value, bindings[j].comment.value, bindings[j].s.value]);
	}
}

