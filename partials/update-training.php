<?php
session_start();
?>
<form id="p-form-training" class="form-inline" ng-submit="tctrl.updateTraining()">
    <div class="form-group">
        <label class="font-weight-bold">締め切り:</label> &nbsp;
        <label id="p-label-deadline" class="font-weight-bold text-danger"></label>
    </div>
    <br />
    <div class="form-group">
        <label>担当者:</label> &nbsp;
        <input type="text" disabled id="p-input-kana" class="form-control" name="p-input-kana" ng-model="tctrl.trainingData.kana">
    </div>
    <br />
    <div class="form-group">
        <label>帳票名:</label> &nbsp;
        <input type="text" disabled id="p-input-task" class="form-control" name="p-input-task" ng-model="tctrl.trainingData.task">
    </div>
    <br />
    <div class="form-group">
        <label>開始日:</label> &nbsp;
        <input type="text" id="p-datepicker-1" class="form-control" name="p-input-start" placeholder="開始日" date-pickers readonly>
    </div>
    <br />
    <div class="form-group">
        <label>終了日:</label> &nbsp;
        <input type="text" id="p-datepicker-2" class="form-control" name="p-input-end" placeholder="終了日" date-pickers readonly>
    </div>
    <br />
    <div class="form-group">
        <label>言付け:</label> &nbsp;
        <textarea type="text" class="form-control" name="p-input-msg" rows="3" ng-model="tctrl.trainingData.msg" placeholder="言付け/メモ"></textarea>
    </div>
    <br />
    <div class="form-group">
        <label>フラグ:</label> &nbsp;
        <select id="p-select-status" class="form-control pointer" name="p-select-status" ng-model="tctrl.trainingData.status">
            <option value="-1" selected>Update</option>
            <?php
                $selectOption = '<option value="2">Finished 90%</option>';
                if (!isset($_SESSION["user"])) {
                    $selectOption .= '<option value="3">Finished 100%</option>';
                    $selectOption .= '<option value="4">Incomplete Task</option>';
                }
                echo $selectOption;
            ?>
        </select>
    </div>
    <div class="col"></div>
    <br />
    <input type="hidden" ng-model="tctrl.trainingData.oldStartDate">
    <input type="hidden" ng-model="tctrl.trainingData.oldEndDate">
    <input type="hidden" id="p-input-duration" class="form-control" value={{tctrl.trainingData.duration}}>
    <input type="hidden" id="p-input-elapseddays" class="form-control" value={{tctrl.trainingData.elapseddays}}>
    <input type="hidden" id="p-input-elapsed" class="form-control" value={{tctrl.trainingData.elapsed}}>
    <button type="submit" id="p-input-submit" class="btn btn-success" class="pointer">
        <i class="fa fa-save" aria-hidden="true"></i> Submit</button>
</form>