<div class="video">
<% if $Provider == "youtube" %>
<a href="https://www.youtube.com/watch?rel=0&v={$Video}">
    <img src="{$Image.FillMax(320,240).URL}" alt="<% if $Title %>{$Title.XML}<% end_if %>" width="320" height="240" loading="lazy">
</a>
<% else_if $Provider == "vimeo" %>
<a href="https://vimeo.com/{$Video}">
    <img src="{$Image.FillMax(320,240).URL}" alt="<% if $Title %>{$Title.XML}<% end_if %>" width="320" height="240" loading="lazy">
</a>
<% else %>
    <!-- provider={$Provider.XML} -->
<% end_if %>
</div>
