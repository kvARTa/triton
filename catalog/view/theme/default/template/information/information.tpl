<?php echo $header; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>

<?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content">
 <div class="product-list">
<?php echo $content_top; ?>

  <h1><?php echo $heading_title; ?></h1>
  <?php echo $description; ?>
    <?php echo $content_bottom; ?></div></div>
<?php echo $footer; ?>