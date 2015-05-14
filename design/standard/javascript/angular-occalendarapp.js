var OCCalendarServices = angular.module('OCCalendarServices', ['ngResource']);

OCCalendarServices
  .factory('CalendarSearch', ['$resource', function($resource){    
    return $resource('/calendar/search', {}, {
      search: {method:'GET', params:{}, isArray:false}
    });
  }]);

var OCCalendarApp = angular.module('OCCalendarApp', [
    'daterangepicker', 'OCCalendarServices'
]);

OCCalendarApp.controller('CalendarCtrl', ['$scope','CalendarSearch',
  function($scope,CalendarSearch) {
          
    $scope.selectedDate = {startDate: null, endDate: null};
    $scope.selectedWhen = 'today';    
    $scope.selectedWhatDetail = [];
    $scope.selectedWhereDetail = [];    
    $scope.selectedTarget = [];    
    $scope.selectedCategory = [];
    
    $scope.query = {
      'text': null,
      'when': null,
      'dateRange': [],
      'what': [],
      'where': [],
      'target': [],
      'category': []      
    };
    $scope.query.when = $scope.selectedWhen;
    
    var formatQuery = function(query){
      var clean = {
        'text': query.text,
        'when': query.when,
        'dateRange[]': query.dateRange,
        'what[]': query.what,
        'where[]': query.where,
        'target[]': query.target,
        'category[]': query.category
      }
      return clean;
    };
    
    var search = function() {
      $scope.query.text = $scope.queryText;
      
      $scope.query.what = [];
      if (typeof $scope.selectedWhat == 'object')
        $scope.query.what.push( $scope.selectedWhat.id );
      for (var n = 0 ; n < $scope.selectedWhatDetail.length ; n++) {
        $scope.query.what.push($scope.selectedWhatDetail[n].id);
      }

      $scope.query.where = [];
      if (typeof $scope.selectedWhere == 'object')
        $scope.query.where.push( $scope.selectedWhere.id );
      for (var n = 0 ; n < $scope.selectedWhereDetail.length ; n++) {
        $scope.query.where.push($scope.selectedWhereDetail[n].id);
      }

      $scope.query.target = [];
      for (var n = 0 ; n < $scope.selectedTarget.length ; n++) {
        $scope.query.target.push($scope.selectedTarget[n].id);
      }

      $scope.query.category = [];
      for (var n = 0 ; n < $scope.selectedCategory.length ; n++) {
        $scope.query.category.push($scope.selectedCategory[n].id);
      }
      
      CalendarSearch.search(formatQuery($scope.query),function(data){
        $scope.what = data.facets.what;
        $scope.where = data.facets.where;;
        $scope.target = data.facets.target;
        $scope.category = data.facets.category;
        $scope.events = data.result.events;
        $scope.count = data.result.count;
      });
    }
    
    search();
    
    $scope.updateText = function(){      
      search();
    };
    
    $scope.$watch('selectedDate', function(newDate) {
      if (newDate.startDate != null) {
        $scope.selectedWhen = 'range';
        $scope.query.when = $scope.selectedWhen;
        var startDate = moment( $scope.selectedDate.startDate );
        var endDate = moment( $scope.selectedDate.endDate );        
        $scope.query.dateRange = [startDate.format('YYYYMMDD'),endDate.format('YYYYMMDD')];
        search();
      }
    }, false);
    
    $scope.selectWhen = function(string){
      $scope.selectedWhen = string;
      $scope.selectedDate = {startDate: null,endDate:null};
      $scope.query.when = $scope.selectedWhen;
      $scope.query.dateRange = [];
      search();
    };
    
    $scope.isWhenSelected = function(string){
      return string == $scope.query.when;
    };
        
    $scope.selectWhatDetail = function(item){
      if ($scope.isWhatDetailSelected(item)) {
        for (var n = 0 ; n < $scope.selectedWhatDetail.length ; n++) {
          if ($scope.selectedWhatDetail[n].id == item.id) {
            var removedObject = $scope.selectedWhatDetail.splice(n,1);
            removedObject = null; break;
          }
        }
      }else{
        $scope.selectedWhatDetail.push( item );
      }
      search();
    };
    
    $scope.isWhatDetailSelected = function(item){
      return $.inArray(item.id, $scope.query.what) > -1;
    };
    
    $scope.updateWhat = function(){
      $scope.selectedWhatDetail = [];
      search();
    };
    
    $scope.selectWhereDetail = function(item){
      if ($scope.isWhereDetailSelected(item)) {
        for (var n = 0 ; n < $scope.selectedWhereDetail.length ; n++) {
          if ($scope.selectedWhereDetail[n].id == item.id) {
            var removedObject = $scope.selectedWhereDetail.splice(n,1);
            removedObject = null; break;
          }
        }
      }else{
        $scope.selectedWhereDetail.push( item );
      }
      search();
    };
    
    $scope.isWhereDetailSelected = function(item){
      return $.inArray(item.id, $scope.query.where) > -1;
    };
    
    $scope.updateWhere = function(){
      $scope.selectedWhereDetail = [];
      search();
    }
        
    $scope.selectTarget = function(item){
      if ($scope.isTargetSelected(item)) {
        for (var n = 0 ; n < $scope.selectedTarget.length ; n++) {
          if ($scope.selectedTarget[n].id == item.id) {
            var removedObject = $scope.selectedTarget.splice(n,1);
            removedObject = null; break;
          }
        }
      }else{
        $scope.selectedTarget.push( item );
      }
      search();
    };
    
    $scope.isTargetSelected = function(item){
      return $.inArray(item.id, $scope.query.target) > -1;
    };
        
    $scope.selectCategory = function(item){
      if ($scope.isCategorySelected(item)) {
        for (var n = 0 ; n < $scope.selectedCategory.length ; n++) {
          if ($scope.selectedCategory[n].id == item.id) {
            var removedObject = $scope.selectedCategory.splice(n,1);
              removedObject = null; break;
          }
        }
      }else{
        $scope.selectedCategory.push( item );
      }
      search();
    };
    
    $scope.isCategorySelected = function(item){
      return $.inArray(item.id, $scope.query.category) > -1;
    };
  
}]);