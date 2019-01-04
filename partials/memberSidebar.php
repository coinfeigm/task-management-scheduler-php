<nav class="navbar navbar-default memberSidebarNav">
    <small class="header" href="#">
        Manage Reviewers
    </small>
</nav>

<div id="memberSidebarContent">
    <div ng-controller="reviewerCtrl as vm" ng-init="vm.deleteReviewersDisabled=true">
        <div id="reviewerOptions">
            <b>Reviewers:</b>
            <button type='button' class='btn btn-default btn-sm pointer' data-toggle='modal' data-target='#reviewerModal' ng-click='vm.addNewReviewers()'>
                <i class='fa fa-plus' aria-hidden='true'></i>
            </button>
            <button type='button' class='btn btn-default btn-sm pointer' ng-click='vm.deleteReviewers()' ng-disabled='vm.deleteReviewersDisabled'>
                <i class='fa fa-trash' aria-hidden='true'></i>
            </button>
        </div>

        <table id="reviewersTable" class="table table-hover" width="100%" ng-if="vm.rAuthorized" datatable dt-options="vm.rDtOptions" dt-columns="vm.rDtColumns" dt-instance="vm.rDtInstance"></table>

        <div class="modal fade" id="memberListModal" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Reviewer Members List</h4>
                        <button type="button" class="close pointer" ng-click="vm.closeMemberListModal()">&times;</button>
                    </div>

                    <div class="modal-body">
                        <b>担当者 :</b> {{vm.memberForm.kananame}} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <b>Name :</b> {{vm.memberForm.name}} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <b>班 :</b> {{vm.memberForm.team}}

                        <div id="reviewerOptions">
                            <b>Options:</b>
                            <button type='button' class='btn btn-default btn-sm pointer' ng-click='vm.deleteReviewerMembers()' ng-disabled='vm.deleteReviewerMembersDisabled'>
                                <i class='fa fa-trash' aria-hidden='true'></i>
                            </button>
                        </div>

                        <table id="memberListTable" class="table table-hover modal-table" width="100%" ng-if="vm.mlAuthorized" datatable dt-options="vm.mlDtOptions" dt-columns="vm.mlDtColumns">
                        </table>
                        <br/>
                    </div>
                </div>
            </div>
        </div> <!-- End of Member List Modal -->
    </div> <!-- End of Trainer Div -->

    <div ng-controller="revieweeCtrl as vm">
        <div id="revieweeOptions">
            <b>Reviewees:</b>
        </div>

        <table id="revieweesTable" class="table table-hover" width="100%" ng-if="vm.rAuthorized" datatable dt-options="vm.rDtOptions" dt-columns="vm.rDtColumns" dt-instance="vm.rDtInstance"></table>
    </div> <!-- End of Trainer Div -->

    <br/>

    <div class="pull-right">
        <button  type="button" class="btn btn-primary pointer inline-block" ng-click="vm.reset()">
            <i class="fa fa-undo" aria-hidden="true"></i>
        </button>
        <button  type="button" class="btn btn-success pointer" ng-click="vm.assignReviewee()" ng-disabled="vm.assignRevieweeDisabled" ng-init="vm.assignRevieweeDisabled=true">
            <i class="fa fa-save" aria-hidden="true"></i>
            Assign Reviewee(s)
        </button>
        <button type="button" class="btn btn-danger dismissMemberSidebar pointer">
            <i class="fa fa-times" aria-hidden="true"></i>
            Close
        </button>
    </div>
</div>