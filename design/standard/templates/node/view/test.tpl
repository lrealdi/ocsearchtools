<div class="container" ng-app="OCCalendarApp">
  <div class="content-view-full class-{$node.class_identifier} row">
    <div class="content-main wide">
      
      <h1>test</h1>
      
      <div ng-controller="CalendarCtrl">{literal}
      
        <div class="row">
          <div class="col-md-9">
            <button class="btn btn-default" ng-class="{'btn-primary' : isWhenSelected('today')}" ng-click="selectWhen('today')">Oggi</button>
            <button class="btn btn-default" ng-class="{'btn-primary' : isWhenSelected('tomorrow')}" ng-click="selectWhen('tomorrow')">Domani</button>
            <button class="btn btn-default" ng-class="{'btn-primary' : isWhenSelected('weekend')}" ng-click="selectWhen('weekend')">Weekend</button>
            <div date-range-picker ng-model="selectedDate" id="daterange" class="btn" ng-class="{'btn-primary' : isWhenSelected('range')}" style="display: inline-block">              
              <i class="fa fa-calendar"></i>
              <b class="caret"></b>
            </div>
          </div>
          <div class="col-md-3">
              <input type="text" class="form-control" placeholder="Filter text" ng-model="queryText" ng-change="updateText()">
          </div>
        </div>
        
        <div class="row well well-sm">
          <div class="col-md-6">
            
            <label>Cosa?</label>
            <select class="form-control" ng-options="item.name for item in what track by item.id" ng-model="selectedWhat" ng-change="updateWhat()">
              <option value="">Tutto</option>
            </select>
            
            <ul class="list-inline">
              <li ng-repeat="item in selectedWhat.children">
                <span ng-click="selectWhatDetail(item)" class="btn btn-xs" ng-class="{'btn-primary' : isWhatDetailSelected(item)}" value="{{item.id}}">
                  {{item.name}}
                </span>
              </li>              
            </ul>
            
          </div>
          <div class="col-md-6">
            
            <label>Dove?</label>
            <select class="form-control" ng-options="item.name for item in where track by item.id" ng-model="selectedWhere" ng-change="updateWhere()">
              <option value="">Tutto</option>
            </select>
            
            <ul class="list-inline">
              <li ng-repeat="item in selectedWhere.children">
                <span ng-click="selectWhereDetail(item)" class="btn btn-xs" ng-class="{'btn-primary' : isWhereDetailSelected(item)}" value="{{item.id}}">
                  {{item.name}}
                </span>
              </li>              
            </ul>
            
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-6">
            <ul class="list-inline">
              <li>Destinatari</li>
              <li ng-repeat="item in target">
                <span ng-click="selectTarget(item)" class="btn btn-default btn-xs" ng-class="{'btn-primary' : isTargetSelected(item)}" value="{{item.id}}">
                  {{item.name}}
                </span>
              </li>              
            </ul>
          </div>
          <div class="col-md-6">            
            <ul class="list-inline">
              <li>Tema</li>
              <li ng-repeat="item in category">
                <span ng-click="selectCategory(item)" class="btn btn-default btn-xs" ng-class="{'btn-primary' : isCategorySelected(item)}" value="{{item.id}}">
                  {{item.name}}
                </span>
              </li>              
            </ul>
          </div>
        </div>
        
        
        <div class="row">
          <div class="col-md-12">
            <ul>
              <li ng-repeat="item in events">
                {{item.name}}
              </li>
            </ul>
          </div>
        </div>
        
        <ul class="list-unstyled">
          <li>Currently selectedWhen: <pre>{{ selectedWhen }}</pre> <pre ng-show="isWhen('range')">{{ selectedDate }}</pre></li>
          <li>Currently selectedWhat: <pre>{{ selectedWhat }}</pre></li>
          <li>Currently selectedWhatDetail: <pre>{{ selectedWhatDetail }}</pre></li>
          <li>Currently selectedWhere: <pre>{{ selectedWhere }}</pre></li>
          <li>Currently selectedWhereDetail: <pre>{{ selectedWhereDetail }}</pre></li>
          <li>Currently selectedTarget: <pre>{{ selectedTarget }}</pre></li>
          <li>Currently selectedCategory: <pre>{{ selectedCategory }}</pre></li>
          <li>Text: <pre>{{ queryText }}</pre></li>
          <li>Query: <pre>{{ query }}</pre></li>
        </ul>
        
      {/literal}</div>
      
    </div>
  </div>
</div>

{ezcss_require( 'daterangepicker-bs3.css' )}

{literal}
<script type="text/javascript">
$(document).ready(function() {   
  $('#daterange').daterangepicker({
    opens: 'center'
  });
  $(".select-chosen").chosen();
});
</script>
{/literal}

{ezscript_require(array( 'ezjsc::jquery', 'moment.js', 'daterangepicker.js', 'plugins/chosen.jquery.js' ) )}
<script src={'javascript/angular.js'|ezdesign()}></script>
<script src={'javascript/angular-resource.js'|ezdesign()}></script>
<script src={'javascript/angular-daterangepicker.js'|ezdesign()}></script>
<script src={'javascript/angular-occalendarapp.js'|ezdesign()}></script>

