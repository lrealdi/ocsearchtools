(function ($) {
    $.fn.buttonFilter = function (options) {
        oTable = this;
        var defaults = {
            targetsWrapper: ".dataTableFiltersWrapper",
            targetContainer: ".dataTableFilter",
            target: ".dataTableFilterTerm",
            field: ".dataTableFilterField",
            targetContainerSelectedClass: "selected",
            columns: null
        };
        properties = $.extend(defaults, options);
        return this.each(function () {                        
            $(properties.targetContainer, oTable.parents( '.dataTableContainer' )).css('cursor', 'pointer').bind('click', function(){
                oTable = $(this).parents( '.dataTableContainer' ).find( 'table' ).dataTable();                
                var column = null;
                var self = $(this);
                $.each(properties.columns , function(i,v){                    
                    if (properties.columns[i].field == $(properties.field, self).text() ) {
                        column = properties.columns[i].key;
                    }
                })                
                $(self.parents(properties.targetsWrapper)).find(properties.targetContainer).not(this).removeClass(properties.targetContainerSelectedClass);
                if ($(this).hasClass(properties.targetContainerSelectedClass)) {                    
                    oTable.fnFilter('', column);
                    $(this).removeClass(properties.targetContainerSelectedClass);
                }else{                    
                    oTable.fnFilter($(properties.target, $(this)).text(), column);
                    $(this).addClass(properties.targetContainerSelectedClass);
                }                
            });
        });
    };
})(jQuery);
