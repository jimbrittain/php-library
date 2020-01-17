<?php
    require_once('./wp-blog-header.php');
    if (!headers_sent()) {
        header('Content-Type: application/opensearchdescription+xml; charset=utf-8');
    } 
?>
<?xml version="1.0" encoding="UTF-8"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
    <ShortName>Site Search</ShortName>
    <Description>Search the <?php echo get_bloginfo('name'); ?> Website</Description>
    <Tags><?php echo str_replace(' ', '', strtolower(get_bloginfo('name'))); ?> site</Tags>
    <Contact><?php echo get_bloginfo('admin_email'); ?></Contact>
    <Url type="text/rss+xml"
         rel="results"
         template="<?php echo get_bloginfo('url'); ?>/?s={searchTerms}&amp;feed=rss" />
    <Url type="text/atom+xml"
         rel="results"
         template="<?php echo get_bloginfo('url'); ?>/?s={searchTerms}&amp;feed=atom" />
    <Url type="text/html"
         rel="results"
         template="<?php echo get_bloginfo('url'); ?>/?s={searchTerms}" />
     <LongName><?php echo get_bloginfo('name')?> Site Search</LongName>
     <Image type="image/vnd.microsoft.icon"><?php echo get_bloginfo('url'); ?>/favicon.ico</Image>
     <Query role="example" searchTerms="contact" />
     <Developer>Automatic &amp; Immature Dawn</Developer>
     <SyndicationRight>open</SyndicationRight>
     <Attribution>Content © <?php echo get_bloginfo('name').', '.date(Y); ?></Attribution>
     <AdultContent>false</AdultContent>
     <Language>en-gb</Language>
     <OutputEncoding>UTF-8</OutputEncoding>
     <InputEncoding>UTF-8</OutputEncoding>
 </OpenSearchDescription>;
<?php
    wp_die();
