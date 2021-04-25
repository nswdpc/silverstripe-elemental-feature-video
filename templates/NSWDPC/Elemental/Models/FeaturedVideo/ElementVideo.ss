<% if $ShowTitle && $Title %>
    <<% if $HeadingLevel %>$HeadingLevel<% else %>h2<% end_if %>>
        {$Title}
    </<% if $HeadingLevel %>$HeadingLevel<% else %>h2<% end_if %>>
<% end_if %>
<% if $EmbedHTML %>
    <div class="embed-responsive" style="padding-bottom: {$EmbedAspectRatio}%;">
        $EmbedHTML
    </div>
    <% if $AltVideoURL %>
        <p class="alt-url">
            <a href="$AltVideoURL">
                <% _t('WATCHWITHAUDIODESC','Watch this video with an audio description') %>
            </a>
        </p>
    <% end_if %>
    <% if $Transcript %>
        <h3>Read transcript</h3>
        {$Transcript}
    <% end_if %>
<% end_if %>
