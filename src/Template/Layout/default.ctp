<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="manifest" href="/manifest.json">
    <title>Web Push</title>
    <?php echo $this->Html->css('style.min.css'); ?>

    <?php echo  $this->fetch('meta'); ?>
    <?php echo  $this->fetch('css'); ?>
    <?php echo  $this->fetch('script'); ?>
  </head>
  <body>
    <div class="container">
      <div class="logo">
        <?php echo $this->Html->image('logo.png', array('class' => 'logo animated bounce'));?>
      </div>
      <nav class="navbar navbar navbar-dark bg-web-push">
        <?php echo $this->Html->link('Web Push','/',array('class' => 'navbar-brand'));?>
        <ul class="nav navbar-nav">
          <li class="nav-item">
            <?php echo $this->Html->link('Home','/',array('class' => 'nav-link'));?>
          </li>
          <li class="nav-item">
            <?php echo $this->Html->link('Send Push','/subscribers/sendPush',array('class' => 'nav-link'));?>
          </li>
        </ul>
        <ul class="nav navbar-nav pull-right">
          <li class="nav-item"><button type="button" data-loading-text="subscribing..." class="btn btn-primary js-push-button" autocomplete="off">
          Enable Push Notification
        </button></li>
        </ul>
  </nav>
      <div class="content">
          <?php echo $this->Flash->render(); ?>
          <?php echo $this->fetch('content'); ?>
      </div>

      <div class="footer">
  		</div>
    </div>
    <?php echo $this->Html->script('wp.min');?>
    <?php echo $this->Html->script('wp-func.js');?>
  </body>
</html>
