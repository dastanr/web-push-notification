<div class="subscribers form large-9 medium-8 columns content">
    <?php echo $this->Form->create('subscriber', array('url' => '/subscribers/send_push')); ?>
    <fieldset>
        <legend><?= __('Send Push') ?></legend>
        <hr />
        <?php
            echo $this->Form->input('title',array('class' => 'form-control'));
            echo $this->Form->input('link',array('class' => 'form-control', 'value' => 'http://'));
            echo $this->Form->input('body',array('class' => 'form-control', 'type' => 'textarea'));
        ?>
    </fieldset>
    <br />
    <?= $this->Form->button(__('Send'),array('class' => 'btn btn-success')) ?>
    <?= $this->Form->end() ?>
</div>
