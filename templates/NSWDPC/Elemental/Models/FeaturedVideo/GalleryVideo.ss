<figure>

    <div class="embed video">
    <% if $EmbedURL %>
        <% include NSWDPC/Elemental/Models/FeaturedVideo/Iframe EmbedURL=$EmbedURL, Anchor=$Parent.Anchor, ID=$ID, AllowAttribute=$AllowAttribute %>
    <% else %>
        <!-- no URL for this video was found -->
    <% end_if %>
    </div>

    <% if $Description || $LinkTarget %>
    <figcaption>
        <% if $Description %><p>{$Description.XML}</p><% end_if %>
        <% if $LinkTarget %>
            <p><a href="{$LinkTarget.LinkURL}">{$LinkTarget.Title}</a></p>
        <% end_if %>
    </figcaption>
    <% end_if %>

    <% if $Transcript %>
    <div class="transcript">
        <h4>Transcript</h4>
        <div class="content">
            {$Transcript}
        </div>
    </div>
    <% end_if %>

</figure>
