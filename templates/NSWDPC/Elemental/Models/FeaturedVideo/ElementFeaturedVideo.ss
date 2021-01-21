<div class="{$ElementStyles}">
    <div class="featured-video-element__content">
        <% if $ShowTitle %>
            <h2 class="content-element__title">{$Title.XML}</h2>
        <% end_if %>
        <div>
            <% include LinkedVideo %>
        </div>
        <% if $HTML %>
        <div>
            {$HTML}
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
    </div>
</div>
