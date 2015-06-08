var OCCalendarServices = angular.module('OCCalendarServices', ['ngResource']);

OCCalendarServices
  .factory('CalendarSearch', ['$resource', function($resource){    
    return $resource(OCCalendarAppConfig.baseUrl, {}, {
      search: {method:'GET', params:{}, isArray:false}
    });
  }]);

var OCCalendarApp = angular.module('OCCalendarApp', [
    'daterangepicker', 'OCCalendarServices'
]);

OCCalendarApp.controller('CalendarCtrl', ['$scope','CalendarSearch', '$location',
  function($scope,CalendarSearch,$location) {    
    
    var touched = false;
    
    $scope.isSelected = function(key,match){
      if (typeof $scope.query[key] == 'object'){
        return jQuery.inArray(match, $scope.query[key]) > -1;
      }else{
        return match == $scope.query[key];
      }
    }
    
    $scope.isDisabled = function(key,match){
      var item = getItem(key,match);
      if (typeof item == 'object') {
        return item.is_selectable == 0;
      }else{
        return true;
      }
    }
    
    $scope.select = function(key,value){
      if (typeof $scope.query[key] == 'object'){
        if (jQuery.inArray(value, $scope.query[key]) > -1) {
          for (var n = 0 ; n < $scope.query[key].length ; n++) {
            if ($scope.query[key][n] == value) {
              var removed = $scope.query[key].splice(n,1);
              removed = null;
              break;
            }
          }
        }else{
          $scope.query[key].push(value); 
        }        
      }else{
        $scope.query[key] = value;
      }
      //if (key == 'when' && value == 'today') {
      //  $scope.selectedDateRange = {startDate: moment(),endDate:moment()};
      //}else if (key == 'when' && value == 'tomorrow') {
      //  $scope.selectedDateRange = {startDate: moment().add(1,'days'),endDate:moment().add(1,'days')};
      //}else if (key == 'when' && value == 'weekend') {
      //  $scope.selectedDateRange = {startDate: moment().day('Saturday'),endDate:moment().day("Saturday").add(1,'days')};
      //}
      touched = true;
      get();
    }
    
    $scope.reset = function(key){      
      if (typeof key == 'string') {
        $scope.current[key] = null;
        $scope.query[key] = null;
        $scope.query['_'+key] = [];
        touched = true;
      }else{
        $scope.query = jQuery.extend(true, {}, OCCalendarAppBaseQuery );
        touched = false;
      }          
      get();
    }    
    
    $scope.update = function(key){      
      if ($scope.current[key] && typeof $scope.current[key] == 'object') {
        $scope.query[key] = $scope.current[key].id;        
      }else{
        $scope.query[key] = null;
        $scope.query['_'+key] = [];
      }
      touched = true;
      get();
    }
    
    $scope.text = function(){
      $scope.query.text = $scope.current.text;
      get();
    }
    
    $scope.selected = function(key){      
      return ($scope.current[key] && typeof $scope.current[key] == 'object' ) ? getItem(key,$scope.current[key].id) : null;
    }
    
    $scope.needReset = function(){
      return touched == true;
    }  
    
    var getItem = function (key,id){
      if ($scope[key].length > 0) {
        for (var n = 0 ; n < $scope[key].length ; n++) {
          if ( typeof $scope[key][n] == 'object' && $scope[key][n].id == id) {
            return $scope[key][n];
          }
        }
      }
      return null;
    }
    
    var get = function(){
      $('#ng-spinner').show();      
      CalendarSearch.search(normalize($scope.query),function(data){
        $scope.query = jQuery.extend(true, $scope.query, OCCalendarAppBaseQuery, data.query);
        $scope.what = data.facets.what;        
        $scope.where = data.facets.where;        
        $scope.target = data.facets.target;
        $scope.category = data.facets.category;
        $scope.events = data.result.events;
        $scope.count = data.result.count;
        $scope.current_dates = data.result.current_dates;
        if (typeof $scope.query.where != 'undefined')
          $scope.current.where = getItem('where',$scope.query.where);
        if (typeof $scope.query.what != 'undefined')
          $scope.current.what = getItem('what',$scope.query.what);
        if (typeof $scope.query.text != 'undefined')
          $scope.current.text = $scope.query.text;
        $('#ng-spinner').hide();
        $location.path('/').search($scope.query);
      });
    }
    
    var normalize = function(query){
      return {
        'text': query.text,
        'when': query.when,
        'dateRange[]': query.dateRange,
        'what': query.what,
        '_what[]': query._what,
        'where': query.where,
        '_where[]': query._where,
        'target[]': query.target,
        'category[]': query.category
      }      
    };      
    
    // rivelo il calendario
    $('#ng-calendar').show();
    
    // nascondo lo spinner
    $('#ng-spinner').hide();

    //imposto e lancio query di default
    if (typeof OCCalendarAppConfig.baseQuery == 'undefined' ) {
      var OCCalendarAppBaseQuery = {
        'text': null,
        'when': 'today',
        'dateRange': [],
        'what': null,
        '_what': [],
        'where': null,
        '_where': [],
        'target': [],
        'category': []      
      };
    }else{
      var OCCalendarAppBaseQuery = OCCalendarAppConfig.baseQuery;
    }

    $scope.what = [];
    $scope.where = [];
    $scope.target = [];
    $scope.category = [];    
    $scope.current_dates = [];
    $scope.selectedDateRange = {startDate: null,endDate:null};
    $scope.dateRangeOpts = angular.isObject(OCCalendarAppConfig.dateRangeOptions) ? OCCalendarAppConfig.dateRangeOptions :{};
    $scope.$watch('selectedDateRange', function(newDate,oldDate) {      
      if (newDate.startDate != null) {
        $scope.query.when = 'range';
        var startDate = moment( $scope.selectedDateRange.startDate );
        var endDate = moment( $scope.selectedDateRange.endDate );        
        $scope.query.dateRange = [startDate.format('YYYYMMDD'),endDate.format('YYYYMMDD')];
        get();
      }
    }, false);
    
    $scope.current = {
      'what': null,
      'where': null,
      'text': ''
    }    
    
    var locationQuery = $location['search'].call($location);    
    $scope.query = jQuery.extend(true, $scope.query, OCCalendarAppBaseQuery, angular.isObject(locationQuery) ? locationQuery :{} );
    get();
    
}]);