<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Subscriber'), ['action' => 'edit', $subscriber->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Subscriber'), ['action' => 'delete', $subscriber->id], ['confirm' => __('Are you sure you want to delete # {0}?', $subscriber->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Subscribers'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Subscriber'), ['action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="subscribers view large-9 medium-8 columns content">
    <h3><?= h($subscriber->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Count') ?></th>
            <td><?= h($subscriber->count) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Browser') ?></th>
            <td><?= h($subscriber->browser) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($subscriber->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($subscriber->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($subscriber->modified) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Register') ?></th>
            <td><?= $subscriber->register ? __('Yes') : __('No'); ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Subscriber') ?></h4>
        <?= $this->Text->autoParagraph(h($subscriber->subscriber)); ?>
    </div>
    <div class="row">
        <h4><?= __('Crpt Key') ?></h4>
        <?= $this->Text->autoParagraph(h($subscriber->crpt_key)); ?>
    </div>
    <div class="row">
        <h4><?= __('Auth') ?></h4>
        <?= $this->Text->autoParagraph(h($subscriber->auth)); ?>
    </div>
</div>
