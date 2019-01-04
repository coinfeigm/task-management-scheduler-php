
<button type='button' class='btn btn-default pointer advancedSearch' data-toggle='modal' data-target='#searchModal' ng-click='tctrl.openSearchModal()' ng-if="tctrl.authorized">
    <i class='fa fa-ellipsis-v' aria-hidden='true'></i>
</button>

<table id="trainingtbl" ng-if="tctrl.authorized" datatable dt-options="tctrl.dtOptions" dt-columns="tctrl.dtColumns" dt-instance="tctrl.dtInstance" class="row-border">
</table>

<div ng-if="!tctrl.authorized" class="loading">
    <div class="spinner">
        <div class="bubble-1"></div>
        <div class="bubble-2"></div>
    </div>
</div>

<div ng-if="tctrl.authorized" class="checkboxfilters" >
    <label class="custom-control custom-checkbox cb">
        <input ng-click="blnOG=!blnOG; tctrl.reload(blnOG, blnFin, blnRet)" type="checkbox" class="custom-control-input" checked>
        <span class="custom-control-indicator"></span>
        <span class="custom-control-description">Ongoing</span>
    </label>

    <label class="custom-control custom-checkbox cb">
        <input ng-click="blnFin=!blnFin; tctrl.reload(blnOG, blnFin, blnRet)" type="checkbox" class="custom-control-input">
        <span class="custom-control-indicator"></span>
        <span class="custom-control-description">Finished</span>
    </label>

    <label class="custom-control custom-checkbox cb">
        <input ng-click="blnRet=!blnRet; tctrl.reload(blnOG, blnFin, blnRet)" type="checkbox" class="custom-control-input">
        <span class="custom-control-indicator"></span>
        <span class="custom-control-description">Retired/Deployed</span>
    </label>
</div>

<div class="modal fade" id="searchModal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Advanced Search</h4>
                <button type="button" class="close pointer" ng-click="tctrl.closeSearchModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form ng-submit="tctrl.submitSearch()">
                    <table class="form-group modal-form-table">
                        <tr>
                            <th>PKG :</th>
                            <td>
                                <select class='form-control pointer pull-left' ng-model='tctrl.selectPackage' ng-change='tctrl.changeFilter(0, tctrl.selectPackage, "add")' ng-init="tctrl.selectPackage='0'" ng-keydown='tctrl.removeFilter(0, $event)'>
                                    <option class="defaultoption" value="0" selected disabled>Select one or more package(s)</option>
                                    <option ng-repeat='x in tctrl.selectPackages track by $index' value='{{x}}'>{{x}}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <td>
                                <div style = "display: flex;flex-wrap: wrap; width: 100%">
                                    <span ng-repeat='x in tctrl.packages track by $index' ng-click='tctrl.changeFilter(0, x, "remove")'>
                                        <span class="badge badge-primary">{{x}} <b class = "pointer">×</b></span>&nbsp;
                                    </span>       
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>病院名 :</th>
                            <td>
                                <select class='form-control pointer pull-left' ng-model='tctrl.selectHospitalname' ng-change='tctrl.changeFilter(2, tctrl.selectHospitalname, "add")' ng-init="tctrl.selectHospitalname='0'" ng-keydown='tctrl.removeFilter(2, $event)'>
                                    <option class="defaultoption" value="0" selected disabled>Select one or more hospital(s)</option>
                                    <option ng-repeat='x in tctrl.selectHospitalnames track by $index' value='{{x}}'>{{x}}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <td>
                                <div style = "display: flex;flex-wrap: wrap; width: 100%">
                                    <span ng-repeat='x in tctrl.hospitalnames' ng-click='tctrl.changeFilter(2, x, "remove")'>
                                        <span class="badge badge-primary">{{x}} <b class = "pointer">×</b></span>&nbsp;
                                    </span>   
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>管理番号 :</th>
                            <td>
                                <select class='form-control pointer pull-left' ng-model='tctrl.selectControlno' ng-change='tctrl.changeFilter(3, tctrl.selectControlno, "add")' ng-init="tctrl.selectControlno='0'" ng-keydown='tctrl.removeFilter(3, $event)'>
                                    <option class="defaultoption" value="0" selected disabled>Select one or more control no(s)</option>
                                    <option ng-repeat='x in tctrl.selectControlnos track by $index' value='{{x}}'>{{x}}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <td>
                                <div style = "display: flex;flex-wrap: wrap; width: 100%">
                                    <span ng-repeat='x in tctrl.controlnos track by $index' ng-click='tctrl.changeFilter(3, x, "remove")'>
                                        <span class="badge badge-primary">{{x}} <b class = "pointer">×</b></span>&nbsp;
                                    </span>   
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>タスク名 :</th>
                            <td>
                                <select class='form-control pointer pull-left' ng-model='tctrl.selectFormname' ng-change='tctrl.changeFilter(4, tctrl.selectFormname, "add")' ng-init="tctrl.selectFormname='0'" ng-keydown='tctrl.removeFilter(4, $event)'>
                                    <option class="defaultoption" value="0" selected disabled>Select one or more form name(s)</option>
                                    <option ng-repeat='x in tctrl.selectFormnames track by $index' value='{{x}}'>{{x}}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <td>
                                <div style = "display: flex;flex-wrap: wrap; width: 100%">
                                    <span ng-repeat='x in tctrl.formnames track by $index' ng-click='tctrl.changeFilter(4, x, "remove")'>
                                        <span class="badge badge-primary">{{x}} <b class = "pointer">×</b></span>&nbsp;
                                    </span>   
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>旧ID :</th>
                            <td>
                                <select class='form-control pointer pull-left' ng-model='tctrl.selectOldid' ng-change='tctrl.changeFilter(5, tctrl.selectOldid, "add")' ng-init="tctrl.selectOldid='0'" ng-keydown='tctrl.removeFilter(5, $event)'>
                                    <option class="defaultoption" value="0" selected disabled>Select one or more old id(s)</option>
                                    <option ng-repeat='x in tctrl.selectOldids track by $index' value='{{x}}'>{{x}}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <td>
                                <div style = "display: flex;flex-wrap: wrap; width: 100%">
                                    <span ng-repeat='x in tctrl.oldids track by $index' ng-click='tctrl.changeFilter(5, x, "remove")'>
                                        <span class="badge badge-primary">{{x}} <b class = "pointer">×</b></span>&nbsp;
                                    </span>   
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>新ID :</th>
                            <td>
                                <select class='form-control pointer pull-left' ng-model='tctrl.selectNewid' ng-change='tctrl.changeFilter(6, tctrl.selectNewid, "add")' ng-init="tctrl.selectNewid='0'" ng-keydown='tctrl.removeFilter(6, $event)'>
                                    <option class="defaultoption" value="0" selected disabled>Select one or more new id(s)</option>
                                    <option ng-repeat='x in tctrl.selectNewids track by $index' value='{{x}}'>{{x}}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <td>
                                <div style = "display: flex;flex-wrap: wrap; width: 100%">
                                    <span ng-repeat='x in tctrl.newids track by $index' ng-click='tctrl.changeFilter(6, x, "remove")'>
                                        <span class="badge badge-primary">{{x}} <b class = "pointer">×</b></span>&nbsp;
                                    </span>   
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Reviewer :</th>
                            <td>
                                <select class='form-control pointer pull-left' ng-model='tctrl.selectReviewer' ng-change='tctrl.changeFilter(7, tctrl.selectReviewer, "add")' ng-init="tctrl.selectReviewer='0'" ng-keydown='tctrl.removeFilter(7, $event)'>
                                    <option class="defaultoption" value="0" selected disabled>Select one or more reviewer(s)</option>
                                    <option ng-repeat='x in tctrl.selectReviewers track by $index' value='{{x}}'>{{x}}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                        <tr>
                            <th></th>
                            <td>
                                <div style = "display: flex;flex-wrap: wrap; width: 100%">
                                    <span ng-repeat='x in tctrl.reviewers track by $index' ng-click='tctrl.changeFilter(7, x, "remove")'>
                                        <span class="badge badge-primary">{{x}} <b class = "pointer">×</b></span>&nbsp;
                                    </span>   
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>班 :</th>
                            <td>
                                <select class='form-control pointer pull-left' ng-model='tctrl.selectTeam' ng-change='tctrl.changeFilter(8, tctrl.selectTeam, "add")' ng-init="tctrl.selectTeam='0'" ng-keydown='tctrl.removeFilter(8, $event)'>
                                    <option class="defaultoption" value="0" selected disabled>Select one or more team(s)</option>
                                    <option ng-repeat='x in tctrl.selectTeams track by $index' value='{{x}}'>{{x}}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                        <tr>
                            <th></th>
                            <td>
                                <div style = "display: flex;flex-wrap: wrap; width: 100%">
                                    <span ng-repeat='x in tctrl.teams track by $index' ng-click='tctrl.changeFilter(8, x, "remove")'>
                                        <span class="badge badge-primary">{{x}} <b class = "pointer">×</b></span>&nbsp;
                                    </span>   
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>担当者 :</th>
                            <td>
                                <select class='form-control pointer pull-left' ng-model='tctrl.selectKananame' ng-change='tctrl.changeFilter(9, tctrl.selectKananame, "add")' ng-init="tctrl.selectKananame='0'" ng-keydown='tctrl.removeFilter(9, $event)'>
                                    <option class="defaultoption" value="0" selected disabled>Select one or more kananame(s)</option>
                                    <option ng-repeat='x in tctrl.selectKananames track by $index' value='{{x}}'>{{x}}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <td>
                                <div style = "display: flex;flex-wrap: wrap; width: 100%">
                                    <span ng-repeat='x in tctrl.kananames track by $index' ng-click='tctrl.changeFilter(9, x, "remove")'>
                                        <span class="badge badge-primary">{{x}} <b class = "pointer">×</b></span>&nbsp;
                                    </span>   
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Name :</th>
                            <td>
                                <select class='form-control pointer pull-left' ng-model='tctrl.selectName' ng-change='tctrl.changeFilter(10, tctrl.selectName, "add")' ng-init="tctrl.selectName='0'" ng-keydown='tctrl.removeFilter(10, $event)'>
                                    <option class="defaultoption" value="0" selected disabled>Select one or more name(s)</option>
                                    <option ng-repeat='x in tctrl.selectNames track by $index' value='{{x}}'>{{x}}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <td>
                                <div style = "display: flex;flex-wrap: wrap; width: 100%">
                                    <span ng-repeat='x in tctrl.names track by $index' ng-click='tctrl.changeFilter(10, x, "remove")'>
                                        <span class="badge badge-primary">{{x}} <b class = "pointer">×</b></span>&nbsp;
                                    </span>   
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Chat Name :</th>
                            <td>
                                <select class='form-control pointer pull-left' ng-model='tctrl.selectChatname' ng-change='tctrl.changeFilter(11, tctrl.selectChatname, "add")' ng-init="tctrl.selectChatname='0'" ng-keydown='tctrl.removeFilter(11, $event)'>
                                    <option class="defaultoption" value="0" selected disabled>Select one or more chatname(s)</option>
                                    <option ng-repeat='x in tctrl.selectChatnames track by $index' value='{{x}}'>{{x}}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <td>
                                <div style = "display: flex;flex-wrap: wrap; width: 100%">
                                    <span ng-repeat='x in tctrl.chatnames track by $index' ng-click='tctrl.changeFilter(11, x, "remove")'>
                                        <span class="badge badge-primary">{{x}} <b class = "pointer">×</b></span>&nbsp;
                                    </span>   
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>開始日 :</th>
                            <td>
                                <input type="text" id="filter-datepicker-1" name="filter-input-start" ng-change="tctrl.changeFilterDate()" class="form-control" ng-model="tctrl.startdatefilter" ng-dblclick = "tctrl.startdatefilter=''" date-pickers placeholder="YYYY/MM/DD" readonly>
                            </td>
                        </tr>
                        <tr>
                            <th>終了日 :</th>
                            <td>
                                <input type="text" id="filter-datepicker-2" name="filter-input-end" class="form-control" ng-model="tctrl.enddatefilter" 
                                ng-dblclick = "tctrl.enddatefilter=''" date-pickers placeholder="YYYY/MM/DD" readonly>
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <td class="btns">
                                <button type="button" value="Refresh" class="btn btn-primary pointer" ng-click='tctrl.changeFilter(0, 0, "refresh")'>
                                    <i class="fa fa-undo" aria-hidden="true"></i>
                                    Clear
                                </button>
                                &nbsp;&nbsp;&nbsp;
                                <button type="submit" value="Submit" class="btn btn-success pointer">
                                    <i class="fa fa-save" aria-hidden="true"></i>
                                    Submit
                                </button>  
                                &nbsp;&nbsp;&nbsp;
                                <button type="button" class="btn btn-danger pointer" ng-click="tctrl.closeSearchModal()">
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