<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $subscriber->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $subscriber->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Subscribers'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="subscribers form large-9 medium-8 columns content">
    <?= $this->Form->create($subscriber) ?>
    <fieldset>
        <legend><?= __('Edit Subscriber') ?></legend>
        <?php
            echo $this->Form->input('subscriber');
            echo $this->Form->input('register');
            echo $this->Form->input('count');
            echo $this->Form->input('browser');
            echo $this->Form->input('crpt_key');
            echo $this->Form->input('auth');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
