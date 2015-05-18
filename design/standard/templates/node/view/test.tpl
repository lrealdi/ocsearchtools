<div class="container" ng-app="OCCalendarApp">
  <div class="content-view-full class-{$node.class_identifier} row">
    <div class="content-main wide">
      
      <h1>
        {$node.name|wash()}
        <i class="fa fa-spinner fa-spin" id="ng-spinner"></i>
      </h1>
      
      {literal}
      <div id="ng-calendar" ng-controller="CalendarCtrl" style="display:none">
        <div id="calendar-search">
          <div class="row">
            <div class="col-md-9">
              <button class="btn btn-default" ng-class="{'btn-primary' : isWhenSelected('today')}" ng-click="selectWhen('today')">Oggi</button>
              <button class="btn btn-default" ng-class="{'btn-primary' : isWhenSelected('tomorrow')}" ng-click="selectWhen('tomorrow')">Domani</button>
              <button class="btn btn-default" ng-class="{'btn-primary' : isWhenSelected('weekend')}" ng-click="selectWhen('weekend')">Weekend</button>
              <div date-range-picker ng-model="selectedDate" id="daterange" class="btn" ng-class="{'btn-primary' : isWhenSelected('range')}" style="display: inline-block">              
                <i class="fa fa-calendar"></i>
                <b class="caret"></b>
              </div>
              <ul class="list-inline" style="display: inline">
                <li>Trovati {{count}} risultati per  <span ng-repeat="current_date in current_dates">{{current_date}} </span></li>
              </ul>
            </div>
            <div class="col-md-3">
              <div class="chosen-container-single">
                <div class="chosen-search">
                  <input type="text" class="form-control" placeholder="Filter text" ng-model="queryText" ng-change="updateText()">
                </div>
              </div>              
            </div>
          </div>
          
          <div class="well well-sm">
            <div class="row">
              <div class="col-md-6">
                <label>Cosa?</label><br />
                <select class="form-control" ng-options="item.name for item in what track by item.id" ng-model="selectedWhat" ng-change="updateWhat()" chosen allow-single-deselect="true" data-placeholder="Seleziona la materia">
                  <option value=""></option>
                </select>
                <ul class="list-inline" style="margin-top: 10px">
                  <li ng-repeat="item in selectedWhat.children">
                    <span ng-click="selectWhatDetail(item)" class="btn btn-xs" ng-class="{'btn-primary' : isWhatDetailSelected(item)}" value="{{item.id}}">
                      {{item.name}} <i class="fa" ng-class="{'fa-times' : isWhatDetailSelected(item)}"></i>
                    </span>
                  </li>              
                </ul>
              </div>
              <div class="col-md-6">
                <label>Dove?</label><br />
                <select class="form-control" ng-options="item.name for item in where track by item.id" ng-model="selectedWhere" ng-change="updateWhere()" chosen allow-single-deselect="true" data-placeholder="Seleziona il comune o l'area turistica">
                  <option value=""></option>
                </select>
                <ul class="list-inline" style="margin-top: 10px">
                  <li ng-repeat="item in selectedWhere.children">
                    <span ng-click="selectWhereDetail(item)" class="btn btn-xs" ng-class="{'btn-primary' : isWhereDetailSelected(item)}" value="{{item.id}}">
                      {{item.name}} <i class="fa" ng-class="{'fa-times' : isWhereDetailSelected(item)}"></i>
                    </span>
                  </li>              
                </ul>            
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <label>Destinatari</label>
                <ul class="list-inline">                
                  <li ng-repeat="item in target">
                    <span ng-click="selectTarget(item)" class="btn btn-default btn-xs" ng-class="{'btn-primary' : isTargetSelected(item), 'disabled': isDisabled(item)}" value="{{item.id}}">
                      {{item.name}} <i class="fa" ng-class="{'fa-times' : isTargetSelected(item)}"></i>
                    </span>
                  </li>              
                </ul>
              </div>
              <div class="col-md-6">            
                <label>Tema</label>
                <ul class="list-inline">                
                  <li ng-repeat="item in category">
                    <span ng-click="selectCategory(item)" class="btn btn-default btn-xs" ng-class="{'btn-primary' : isCategorySelected(item), 'disabled': isDisabled(item)}" value="{{item.id}}">
                      {{item.name}} <i class="fa" ng-class="{'fa-times' : isCategorySelected(item)}"></i>
                    </span>
                  </li>              
                </ul>
              </div>
            </div>
          </div>                
        </div>
                
        <div class="row" ng-repeat="item in events">
          <div class="col-xs-2 col-sm-1 text-center">
            <h2>{{item.day.day}}</h2>
            <h3>{{item.day.month}}</h3>
          </div>
          <div class="col-xs-10 col-sm-11">
            <div ng-repeat="type in item.tipo_evento">
              <h2>{{type.name}}</h2>
              <div ng-repeat="event in type.events">
                <h4><a href="{{event.href}}">{{event.name}}</a></h4>            
                <p style="white-space: pre-wrap;">{{event.orario_svolgimento}}</p>
                <p><strong ng-repeat="where in event.comune">{{where.name}}</strong> {{event.luogo_svolgimento}}</p>                        
                <p>                                                
                  <small ng-repeat="target in event.utenza_target">{{target.name}}</small>
                  <small ng-repeat="tema in event.tema">{{tema.name}}</small>
                </p>                    
              </div>
            </div>              
          </div>
          
        </div>
        
      </div>
      {/literal}
      
      {* angular debug
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
      *}
      
    </div>
  </div>
</div>

{ezcss_require( 'daterangepicker-bs3.css','chosen.css' )}


<script type="text/javascript">
var OCCalendarAppUrl = "{concat('calendar/search/',$node.node_id)|ezurl(no)}";
{literal}
$(document).ready(function() {   
  $('#daterange').daterangepicker({
    opens: 'center'
  });
});
{/literal}
</script>

<style>
.chosen-container-single .chosen-single{ldelim}background-image:none{rdelim}
.btn.disabled{ldelim}opacity:0.4{rdelim}
</style>


{ezscript_require(array( 'ezjsc::jquery', 'moment.js', 'daterangepicker.js', 'plugins/chosen.jquery.js' ) )}
<script src={'javascript/angular.js'|ezdesign()}></script>
<script src={'javascript/angular-resource.js'|ezdesign()}></script>
<script src={'javascript/angular-daterangepicker.js'|ezdesign()}></script>
<script src={'javascript/angular-chosen.js'|ezdesign()}></script>
<script src={'javascript/angular-occalendarapp.js'|ezdesign()}></script>

