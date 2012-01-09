var Index = {
    init : function() {           
        $('#mysql-grid').jqGrid('filterToolbar', {
            searchOnEnter : true,
            autosearch : true 
        });      
    }    
}