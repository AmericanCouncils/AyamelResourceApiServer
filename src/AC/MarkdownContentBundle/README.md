# MarkdownContentBundle #

This bundle allows you to create pages of content with Markdown syntax, and organize them statically.  Generally most useful for documentation purposes.

## Usage ##

In configuration a content root directory can be defined.  If not defined, it defaults to `%kernel.root_dir%/static_content`.  In this directory you can place markdown files.  For directory-level pages, you can name the file `index.md`, and it will be used automatically.  At the top of each file, you may optionally include a meta-data header in YAML format, which can be used to override default config values on a per-page basis.

## Configuration ##

* `mdcontent.root_dir` - 
* `mdcontent.route_prefix` - 
* `mdcontent.default_template` -
* `mdcontent.page_cache_lifetime` - 

## Usage ##

Create a directory somewhere in your project to be used as the `mdcontent.root_dir`, and place content pages in that directory.  They should be organized in the same way as you want them browsable on the web.  A route for every file will automatically be added during bootstrap.

## Example content page ##

A content page is basically any text file written in markdown syntax, with an optional YAML metadata header.  If the header is present, the `---` must be the first line of the file.

    ---
    foo: bar
    bar: ["baz","stuff"]
    ---
    # Page title #
    
    This is about the time I... blah blah blah
    
    ## Subheading ##
    
    Continue as you would in any markdown file. Here are three examples:
    
        * foo
        * bar
        * baz
        
    That's all, folks.