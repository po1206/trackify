<?php if (!empty($_SESSION['message_display'])) : ?>
    <div class="alert alert-success divFirstExplanation">
        <button data-dismiss="alert" class="close closeClickExplanation" type="button">×</button>
        <strong><?php echo $_SESSION['message_display']; $_SESSION['message_display'] = ''; ?></strong>
    </div>
<?php endif; ?>