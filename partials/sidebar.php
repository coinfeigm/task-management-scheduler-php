<nav class="navbar navbar-default sidebarNav">
    <small class="header" href="#">
        Manage Tasks
    </small>
</nav>

<div id="sidebarContent">
    <div ng-controller="memberCtrl as vm" ng-init="vm.deleteMembersDisabled=true">

        <div id="memberOptions">
            <b>Members:</b>
            <button type='button' class='btn btn-default btn-sm pointer' data-toggle='modal' data-target='#memberModal' ng-click='vm.addNewMember(memberForm)'>
                <i class='fa fa-plus' aria-hidden='true'></i>
            </button>
            <button type='button' class='btn btn-default btn-sm pointer' ng-click='vm.deleteMembers()' ng-disabled='vm.deleteMembersDisabled'>
                <i class='fa fa-trash' aria-hidden='true'></i>
            </button>
        </div>

        <table id="membersTable" class="table table-hover" width="100%" ng-if="vm.mAuthorized" datatable dt-options="vm.mDtOptions" dt-columns="vm.mDtColumns" ng-init="vm.deleteMemberTasksDisabled=true" dt-instance="vm.mDtInstance"></table>

        <div class="modal fade" id="memberModal" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add / Edit Member</h4>
                        <button type="button" class="close pointer" ng-click="vm.closeMemberModal()">&times;</button>
                    </div>

                    <div class="modal-body">
                        <form name="memberForm" ng-submit="vm.submitMember()">
                            <table class="form-group modal-form-table">
                                <tr>
                                    <th>担当者 :</th>
                                    <td>
                                        <input type="text" id="kananame" class="form-control" ng-model="vm.memberForm.kananame" placeholder="担当者" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Name :</th>
                                    <td>
                                        <input type="text" id="name" class="form-control" ng-model="vm.memberForm.name" placeholder="Name" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Chat Name :</th>
                                    <td>
                                        <input type="text" id="chatname" class="form-control" ng-model="vm.memberForm.chatname" placeholder="Chat Name">
                                    </td>
                                </tr>
                                <tr>
                                    <th>班 :</th>
                                    <td>
                                        <select ng-model="vm.memberForm.team" id="team" class="form-control pointer" required>
                                            <option value=""></option>
                                            <option value="A">A</option>
                                            <option value="F">F</option>
                                            <option value="N">N</option>
                                            <option value="日">日</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>医療所属 :</th>
                                    <td>
                                        <input type="text" id="medaffiliation" class="form-control" list="list_medaffiliation" ng-model="vm.memberForm.medaffiliation" required>
                                        <datalist id="list_medaffiliation">
                                            <option ng-repeat="x in vm.medaffiliations">{{x}}</option>
                                        </datalist>
                                    </td>
                                </tr>
                                <tr>
                                    <th>備考 :</th>
                                    <td>
                                        <textarea id="remarks" class="form-control" rows="3" ng-model="vm.memberForm.remarks" placeholder="備考"></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th>状態: </th>
                                    <td>
                                        <select ng-model="vm.memberForm.statusflg" id="statusflg" class="form-control pointer" ng-change="vm.selectMemberStatus()" required>
                                            <option></option>
                                            <option value="0">&#xf0fd;&nbsp;&nbsp; Hospital Project</option>
                                            <option value="1">&#xf187;&nbsp;&nbsp; Other Projects / AEON</option>
                                            <option value="2">&#xf072;&nbsp;&nbsp; Deployed</option>
                                            <option value="3">&#xf057;&nbsp;&nbsp; Retired</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><input type="hidden" ng-model="vm.memberForm.memberid"></th>
                                    <td><input type="hidden" ng-model="vm.memberForm.oldstatusflg"></td>
                                </tr>
                                <tr>
                                    <th></th>
                                    <td>
                                        <button type="submit" value="Submit" class="btn btn-success pointer">
                                            <i class="fa fa-save" aria-hidden="true"></i>
                                            Submit
                                        </button>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <button type="button" class="btn btn-danger pointer" ng-click="vm.closeMemberModal()">
                                            <i class="fa fa-times" aria-hidden="true"></i>
                                            Cancel
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div> <!-- End of Member Modal -->
        <div class="modal fade" id="taskListModal" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Member Tasks List</h4>
                        <button type="button" class="close pointer" ng-click="vm.closeTaskListModal()">&times;</button>
                    </div>

                    <div class="modal-body">
                        <b>担当者 :</b> {{vm.memberForm.kananame}} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <b>Name :</b> {{vm.memberForm.name}} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <b>班 :</b> {{vm.memberForm.team}}

                        <div id="memberOptions">
                            <b>Options:</b>
                            <button type='button' class='btn btn-default btn-sm pointer' ng-click='vm.deleteMemberTasks()' ng-disabled='vm.deleteMemberTasksDisabled'>
                                <i class='fa fa-trash' aria-hidden='true'></i>
                            </button>
                            <button type='button' class='btn btn-default btn-sm pointer' ng-click='vm.sortTaskUp()' ng-disabled='vm.deleteMemberTasksDisabled'>
                                <i class='fa fa-arrow-up' aria-hidden='true'></i>
                            </button>
                            <button type='button' class='btn btn-default btn-sm pointer' ng-click='vm.sortTaskDown()' ng-disabled='vm.deleteMemberTasksDisabled'>
                                <i class='fa fa-arrow-down' aria-hidden='true'></i>
                            </button>
                        </div>

                        <table id="taskListTable" class="table table-hover modal-table" width="100%" ng-if="vm.tlAuthorized" datatable dt-options="vm.tlDtOptions" dt-columns="vm.tlDtColumns">
                        </table>
                        <br/>
                    </div>
                </div>
            </div>
        </div> <!-- End of Task List Modal -->
    </div> <!-- End of Task Div -->

    <div ng-controller="taskCtrl as vm" ng-init="vm.disablePackageSelect=false; vm.newPackageLink=true; vm.newPackageDiv=false; vm.disableHospitalSelect=false; vm.newHospitalLink=true; vm.newHospitalDiv=false; vm.deleteTasksDisabled=true">

        <form class='form-inline' id="taskOptions">
            <div class='form-group'>
                <b>Tasks:</b>
                <select class='form-control pointer' ng-model='vm.package' ng-change='vm.selectPackage(vm.package)' ng-init="vm.package='0'">
                    <option value="0">All PKG</option>
                    <option ng-repeat='x in vm.packages track by $index' value='{{x}}'>{{x}}</option>
                </select>
            </div>
            <button type='button' class='btn btn-default btn-sm pointer' data-toggle='modal' data-target='#taskModal' ng-click='vm.addNewTask(taskForm)'>
                <i class='fa fa-plus' aria-hidden='true'></i>
            </button>
            <button type='button' class='btn btn-default btn-sm pointer' ng-disabled='vm.deleteTasksDisabled' ng-click='vm.deleteTasks()'>
                <i class='fa fa-trash' aria-hidden='true'></i>
            </button>
            <button type='button' class='btn btn-default btn-sm pointer' ng-disabled='vm.deleteTasksDisabled' ng-click='vm.importWbs()'>
                <i class='fa fa-pie-chart' aria-hidden='true'></i>
            </button>
        </form>

        <table id="tasksTable" class="table table-hover" width="100%" ng-if="vm.tAuthorized" datatable dt-options="vm.tDtOptions" dt-columns="vm.tDtColumns" dt-instance="vm.tDtInstance"></table>

        <div class="modal fade" id="taskModal" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add / Edit Task</h4>
                        <button type="button" class="close pointer" ng-click="vm.closeTaskModal()">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form name="taskForm" ng-submit="vm.submitTask()">
                            <table class="form-group modal-form-table">
                                <tr>
                                    <th>PKG :</th>
                                    <td>
                                        <select id="package" ng-model="vm.taskForm.package" class="form-control pointer" ng-if="!vm.disablePackageSelect" ng-disabled="vm.disablePackageSelect" ng-change="vm.getHospitals()" required>
                                            <option></option>
                                            <option ng-repeat="x in vm.packages track by $index" value="{{x}}">{{x}}</option>
                                        </select>
                                        <div ng-if="vm.newPackageDiv">
                                            <input type="text" id="package" class="form-control" ng-model="vm.taskForm.package" placeholder="PKG" required>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th></th>
                                    <td>
                                        <a href="#" ng-click="vm.addNewPackage()" ng-if="vm.newPackageLink">
                                            <i class="fa fa-plus" aria-hidden="true"></i>&nbsp;New Package
                                        </a>
                                        <div ng-if="vm.newPackageDiv">
                                            <a href="#" ng-click="vm.cancelAddPackage()">
                                                <i class="fa fa-times" aria-hidden="true"></i>&nbsp;Cancel Add
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>病院名 :</th>
                                    <td>
                                        <select id="hospital" class="form-control pointer" ng-model="vm.taskForm.hospitalno" ng-if="!vm.disableHospitalSelect" ng-disabled="vm.disableHospitalSelect" required>
                                            <option></option>
                                            <option ng-repeat="x in vm.hospitals" value="{{x[0]}}">{{x[1]}}</option>
                                        </select>
                                        <div ng-if="vm.newHospitalDiv">
                                            <input type="text" id="hospital" class="form-control" ng-model="vm.taskForm.hospitalname" placeholder="病院名" required>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th></th>
                                    <td>
                                        <a href="#" ng-click="vm.addNewHospital()" ng-if="vm.newHospitalLink">
                                            <i class="fa fa-plus" aria-hidden="true"></i>&nbsp;New Hospital
                                        </a>
                                        <div ng-if="vm.newHospitalDiv">
                                            <a href="#" ng-click="vm.cancelAddHospital()">
                                                <i class="fa fa-times" aria-hidden="true"></i>&nbsp;Cancel Add
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>管理番号 :</th>
                                    <td><input type="text" id="controlno" class="form-control" ng-model="vm.taskForm.controlno" placeholder="管理番号"></td>
                                </tr>
                                <tr>
                                    <th>タスク名 :</th>
                                    <td><input type="text" id="formname" class="form-control" ng-model="vm.taskForm.formname" placeholder="タスク名" required></td>
                                </tr>
                                <tr>
                                    <th>旧ID :</th>
                                    <td><input type="text" id="oldid" class="form-control" ng-model="vm.taskForm.oldid" placeholder="旧ID"></td>
                                </tr>
                                <tr>
                                    <th>新ID :</th>
                                    <td><input type="text" id="newid" class="form-control" ng-model="vm.taskForm.newid" placeholder="新ID"></td>
                                </tr>
                                <tr>
                                    <th>工数(人日) :</th>
                                    <td><input type="text" id="duration" class="form-control" ng-model="vm.taskForm.duration" placeholder="工数(人日)" required></td>
                                </tr>
                                <tr>
                                    <th><input class="form-control" type="hidden" ng-model="vm.taskForm.taskno"></th>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <th></th>
                                    <td>
                                        <button type="submit" value="Submit" class="btn btn-success pointer">
                                            <i class="fa fa-save" aria-hidden="true"></i>
                                            Submit
                                        </button>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <button type="button" class="btn btn-danger pointer" ng-click="vm.closeTaskModal()">
                                            <i class="fa fa-times" aria-hidden="true"></i>
                                            Cancel
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div> <!-- End of Task Modal -->

        <div class="modal fade" id="wbsModal" data-backdrop="static">
            <div class="modal-dialog expand">
                <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"></i>&nbsp;【GAS】WBS実績</h4>
                    <button type="button" class="close pointer" data-dismiss="modal" ng-click="vm.closeWBSModal()">&times;</button>
                </div>

                <div class="modal-body">
                    <span ng-if="vm.wbsAuthorized" class="daterange">出力期間: {{vm.wbsStartDate}} ～ {{vm.wbsEndDate}}</span>
                    <div class="centermargin" >
                        <table id="wbstbl" ng-if="vm.wbsAuthorized" datatable dt-options="vm.wbsDtOptions" dt-columns="vm.wbsDtColumns" dt-instance="vm.wbsDtInstance" class="row-border">
                        </table>

                        <div ng-if="!vm.wbsAuthorized" class="loadingWBS">
                            <div class="spinner">
                                <div class="bubble-1"></div>
                                <div class="bubble-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div> <!-- End of WBS Modal -->
    </div> <!-- End of Task Div -->

    <div class="withtopmargin">
        <div class="pull-right withtopmrgnbtn">
            <button  type="button" class="btn btn-primary pointer inline-block" ng-click="vm.reset()">
                <i class="fa fa-undo" aria-hidden="true"></i>
            </button>
            <button  type="button" class="btn btn-success pointer" ng-click="vm.assignTraining()" ng-disabled="vm.assignTaskDisabled">
                <i class="fa fa-save" aria-hidden="true"></i>
                Assign Task(s)
            </button>
            <button type="button" class="btn btn-danger dismissSidebar pointer">
                <i class="fa fa-times" aria-hidden="true"></i>
                Close
            </button>
        </div>

        <label class="custom-control custom-checkbox cb nobotmargin">
            <input type="checkbox" class="custom-control-input" id="priorityCheckbox" ng-model="vm.priorityCheckbox" ng-click="vm.checkTaskOptions(true)" ng-disabled="vm.assignTaskDisabled">
            <span class="custom-control-indicator"></span>
            <span class="custom-control-description" for="priorityCheckbox">Priority Task(s)</span>
        </label>

        <label class="custom-control custom-checkbox cb nobotmargin">
            <input type="checkbox" class="custom-control-input" id="nextTaskCheckbox" ng-model="vm.nextTaskCheckbox" ng-click="vm.checkTaskOptions(false)" ng-disabled="vm.assignTaskDisabled">
            <span class="custom-control-indicator"></span>
            <span class="custom-control-description" for="nextTaskCheckbox">Next Task(s)</span>
        </label>
    </div>
</div>