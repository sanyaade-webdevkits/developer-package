<?php defined('C5_EXECUTE') or die(_("Access Denied.")); ?>
<?php
	$pageVersion = $c->vObj;
	$block = Block::getByName('My_Site_Name');

	$title = $pageVersion->cvName;
	$description = $pageVersion->cvDescription;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<?php Loader::element('header_required'); ?>
		<meta http-equiv="Content-Language" content="ja" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<link rel="stylesheet" href="<?php echo $this->getStyleSheet('main.css'); ?>" type="text/css" media="screen" />
		<link rel="stylesheet" href="<?php echo $this->getStyleSheet('oocss.css'); ?>" type="text/css" media="screen" />
		<link rel="stylesheet" href="<?php echo $this->getStyleSheet('typography.css'); ?>" type="text/css" media="screen" />
		<script src="<?php echo $this->getThemePath()?>/js/syntax_highlighter/shCore.js" type="text/javascript"></script>
		<script src="<?php echo $this->getThemePath()?>/js/syntax_highlighter/shAutoloader.js" type="text/javascript"></script>
		<script src="<?php echo $this->getThemePath()?>/js/sh.js" type="text/javascript"></script>
	</head>
	<body>
		<div class="page">

<div class="head">
	<div class="inner gs960">
		<div class="mod pgdesc">
			<div class="inner">
				<div class="hd">
					<h1><a title="<?php echo ($block && $block->bID) ? $block->display() : SITE; ?>" href="<?php echo DIR_REL?>/"><?php echo ($block && $block->bID) ? $block->display() : SITE; ?></a></h1>
				</div>
				<div class="bd">
					<?php if ($description) : ?>
						<p><?php echo $title." - ".$description ?></p>
					<?php endif; ?>

					<p class="logo"><a href="<?php echo DIR_REL?>/"><?php echo ($block && $block->bID) ? $block->display() : SITE; ?></a></p>
					<div class="nav">
						<?php
							$nav = new Area('Header Nav');
							$nav->display($c);
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /head -->