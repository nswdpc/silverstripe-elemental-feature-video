<div class="featured-video">

    <% if $ShowTitle %>
        <h2>{$Title.XML}</h2>
    <% end_if %>

    <figure>

        <% if $Image %>

            <div class="linked video">
                <% include NSWDPC/Elemental/Models/FeaturedVideo/LinkedVideo %>
            </div>

        <% else %>

            <div class="embed video">
                <% include NSWDPC/Elemental/Models/FeaturedVideo/Iframe %>
            </div>

        <% end_if %>

            <% if $HTML %>
            <figcaption>
                {$HTML}
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

</div>
