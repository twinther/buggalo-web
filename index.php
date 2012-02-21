<?php

	$content = <<<HTML
	<h2>Exception Viewer</h2>
	<table id="issues" style="width: 100%;">
		<thead>
			<tr>
				<th>ID</th>
				<th>Date</th>
				<th>Name</th>
				<th>Version</th>
				<th>Exception</th>
				<th>Submitter</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
		<tfoot>
			<th />
			<th />
			<th filter-index="2" filter-name="addon_name" />
			<th filter-index="3" filter-name="addon_version" />
			<th />
			<th filter-index="5" filter-name="ip" />
			<th filter-index="6" filter-name="status" />
		</tfoot>
	</table>
	<script type="text/javascript">
(function($) {
$.fn.dataTableExt.oApi.fnGetColumnData = function ( oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty ) {

    // check that we have a column id
    if ( typeof iColumn == "undefined" ) return new Array();
     
    // by default we only wany unique data
    if ( typeof bUnique == "undefined" ) bUnique = true;
     
    // by default we do want to only look at filtered data
    if ( typeof bFiltered == "undefined" ) bFiltered = true;
     
    // by default we do not wany to include empty values
    if ( typeof bIgnoreEmpty == "undefined" ) bIgnoreEmpty = true;
     
    // list of rows which we're going to loop through
    var aiRows;
     
    // use only filtered rows
    if (bFiltered == true) aiRows = oSettings.aiDisplay;
    // use all rows
    else aiRows = oSettings.aiDisplayMaster; // all row numbers

    // set up data array   
    var asResultData = new Array();
     
    for (var i=0,c=aiRows.length; i<c; i++) {
        iRow = aiRows[i];
        var aData = this.fnGetData(iRow);
        var sValue = aData[iColumn];

        // ignore empty values?
        if (bIgnoreEmpty == true && sValue.length == 0) continue;
 
        // ignore unique values?
        else if (bUnique == true && jQuery.inArray(sValue, asResultData) > -1) continue;
         
        // else push the value onto the result data array
        else asResultData.push(sValue);
    }

    return asResultData;
}}(jQuery));

$.fn.dataTableExt.oApi.fnReloadAjax = function ( oSettings, sNewSource, fnCallback, bStandingRedraw )
{
    if ( typeof sNewSource != 'undefined' && sNewSource != null )
    {
        oSettings.sAjaxSource = sNewSource;
    }
    this.oApi._fnProcessingDisplay( oSettings, true );
    var that = this;
    var iStart = oSettings._iDisplayStart;
     
    oSettings.fnServerData( oSettings.sAjaxSource, [], function(json) {
        /* Clear the old information from the table */
        that.oApi._fnClearTable( oSettings );
         
        /* Got the data - add it to the table */
        var aData =  (oSettings.sAjaxDataProp !== "") ?
            that.oApi._fnGetObjectDataFn( oSettings.sAjaxDataProp )( json ) : json;
         
        for ( var i=0 ; i<aData.length ; i++ )
        {
            that.oApi._fnAddData( oSettings, aData[i] );
        }
         
        oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
        that.fnDraw();
         
        if ( typeof bStandingRedraw != 'undefined' && bStandingRedraw === true )
        {
            oSettings._iDisplayStart = iStart;
            that.fnDraw( false );
        }
         
        that.oApi._fnProcessingDisplay( oSettings, false );
         
        /* Callback user function - for event handlers etc */
        if ( typeof fnCallback == 'function' && fnCallback != null )
        {
            fnCallback( oSettings );
        }

				oSettings.fnInitComplete();
    }, oSettings );
};

function fnCreateSelect(aData) {
    var r='<select><option value=""></option>', i, iLen=aData.length;
    for ( i=0 ; i<iLen ; i++ )
    {
        r += '<option value="'+aData[i]+'">'+aData[i]+'</option>';
    }
    return r+'</select>';
}

$(document).ready(function() {
	var oTable = $('#issues').dataTable({
		'bJQueryUI': true,
		'bProcessing' : true,
		'bServerSide' : true,
		'bFiltering' : true,
		'sAjaxSource' : 'issues.json.php',
		'sPaginationType' : 'full_numbers',
		'aaSorting' : [[0, 'desc']],
		'aoColumns' : [
			{'sWidth' : '5%'},
			{'sWidth' : '15%'},
			{'sWidth' : '10%'},
			{'sWidth' : '5%'},
			{'sWidth' : '50%'},
			{'sWidth' : '10%'},
			{'sWidth' : '5%'},
		],
		'aLengthMenu' : [20, 50, 100],
		'iDisplayLength' : 20,
		'fnRowCallback' : function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
			$(nRow).css('cursor', 'pointer');
			$(nRow).click(function() {
				location.href = 'view.php?id=' + aData[0];
			});

			return nRow;
		},
		'fnInitComplete': function(oSettings, json) {
			jQuery.ajax('filters.json.php', {
				'success' : function(data, textStatus, jqXHR) {
					$('th[filter-name]').each(function(idx, elem) {
						filterName = $(elem).attr('filter-name');

						elem.innerHTML = fnCreateSelect(data[filterName]);
						$('select', elem).change(function() {
							oTable.fnFilter($(this).val(), $(elem).attr('filter-index'));
						});
					});
				}
			});

		}
	});

});

	</script>

HTML;

	include('_template.php');
