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

            <% if $HTML || $DerivedFeatureLink %>
            <figcaption>
                <% if $HTML %>
                {$HTML}
                <% end_if %>
                <% if $DerivedFeatureLink %>
                    <% with DerivedFeatureLink %>
                    <p>
                        <a href="{$LinkURL}" rel="noopener noreferrer"><% if $OpenInNewWindow %> target="_blank"<% end_if %>>
                            {$Title}
                        </a>
                    </p>
                    <% end_with %>
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

</div>
