# Documentation

## Templates

The templates provided with this module only provide a starting point to get you going within your own site's theme.

Your developer should override these within the project's theme using [the standard Silverstripe theming standards](https://docs.silverstripe.org/en/5/developer_guides/templates/template_inheritance/#cascading-themes).

Use the variables in the templates provided to define your own templates and layout.


### Video aspect ratio

You will probably want to set an aspect ratio for iframe elements containing videos. The CSS `aspect-ratio` directive can be used for this:

```css
.embed.video > iframe,
.embed.video > video {
    width: 100%;
    height: 100%;
    border: 0;
    aspect-ratio: 16/9;
}
```

This is an improved, simpler method than the older methods using 56.25% top padding with absolute positioning.
