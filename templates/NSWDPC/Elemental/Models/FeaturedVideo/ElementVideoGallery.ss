<div class="{$ElementStyles}">
    <div class="video-element__content">
        <% if $ShowTitle %>
            <h2 class="content-element__title">{$Title.XML}</h2>
        <% end_if %>
        <% if $HTML %>
        <div>
            {$HTML}
        </div>
        <% end_if %>
    </div>
    <% if $SortedVideos %>
        <div class="video-element__videos">
            <% loop $SortedVideos %>
                <div>
                    <% include LinkedVideo %>
                </div>
                <% if $Description %>
                <div>
                    <div>
                        {$Description}
                    </div>
                </div>
                <% end_if %>
                <% if $Transcript %>
                <div>
                    <h3>Transcript</h3>
                    <div>
                        {$Transcript}
                    </div>
                </div>
                <% end_if %>
            <% end_loop %>
        </div>
    <% end_if %>
</div>
