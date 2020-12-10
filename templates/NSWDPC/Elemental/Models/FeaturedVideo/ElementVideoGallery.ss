<div class="{$ElementStyles}">
    <div class="video-element__content">
	    <% if $ShowTitle %>
            <h2 class="content-element__title">{$Title.XML}</h2>
        <% end_if %>
	   $HTML
    </div>
    <% if $Videos %>
        <div class="video-element__videos">
            <% loop $SortedVideos %>
                <div>
                    <a<% if $Title %> title="{$Title.XML}"<% end_if %> href="https://www.youtube.com/watch?v={$Video}">
                        <img src="{$Image.FillMax(320,240).URL}" alt="<% if $Title %>{$Title.XML}<% end_if %>" width="320" height="240">
                    </a>
                </div>
                <div>
                    $Description
                </div>
            <% end_loop %>
        </div>
    <% end_if %>
</div>
