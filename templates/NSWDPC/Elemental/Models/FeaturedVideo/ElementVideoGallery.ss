<div class="video-gallery">
    <% if $ShowTitle %>
        <h2>{$Title.XML}</h2>
    <% end_if %>
    <% if $HTML %>
    <div class="content">
        {$HTML}
    </div>
    <% end_if %>
    <% if $SortedVideos %>
    <div class="videos">
        <% loop $SortedVideos %>
            {$Me}
        <% end_loop %>
    </div>
    <% end_if %>
</div>
