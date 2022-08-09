<figure>

    <div class="embed video">
    <% if $UseVideoThumbnail == 1 %>
        <% if $WatchURL %><a href="{$WatchURL}" rel="noopener"><% end_if %>
        <% if $VideoThumbnail %>
        <img src="{$VideoThumbnail}" class="video-thumbnail" referrerpolicy="no-referrer" loading="lazy">
        <% end_if %>
        <% if $WatchURL %></a><% end_if %>
    <% else if $UseVideoThumbnail == 0 %>
        <% if $WatchURL %><a href="{$WatchURL}" rel="noopener"><% end_if %>
        {$Image.ScaleWidth(720)
        <% if $WatchURL %></a><% end_if %>
    <% else if $EmbedURL %>
        <% include NSWDPC/Elemental/Models/FeaturedVideo/Iframe EmbedURL=$EmbedURL, Anchor=$Parent.Anchor, ID=$ID, AllowAttribute=$AllowAttribute %>
    <% else %>
        <!-- no URL or thumbnail for this video was found -->
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
