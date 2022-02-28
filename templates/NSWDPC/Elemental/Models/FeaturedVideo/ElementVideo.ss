<div class="embed-video">
    <% if $ShowTitle %>
        <h2>{$Title.XML}</h2>
    <% end_if %>
    <% if $EmbedHTML %>

    <figure>

        <div class="embed video embed-responsive"<% if $EmbedAspectRatio %> style="padding-bottom: {$EmbedAspectRatio}%;<% end_if %>">
            $EmbedHTML
        </div>

        <figcaption>
            <% if $Caption %>
                <p>$Caption.XML</p>
            <% end_if %>
            <% if $AltVideoURL %>
                <p class="alt-url">
                    <a href="$AltVideoURL">
                        <%t FeatureVideo.WATCHWITHAUDIODESC 'Watch this video with an audio description' %>
                    </a>
                </p>
            <% end_if %>
        </figcaption>

        <% if $Transcript %>
            <div class="transcript">
                <h3><%t FeatureVideo.TRANSCRIPT 'Transcript' %></h3>
                {$Transcript}
            </div>
        <% end_if %>

    </figure>
    <% end_if %>

</div>
