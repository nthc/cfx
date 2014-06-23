{section name=i loop=$notes}
<div style='background-color:#fff; padding:10px; margin-top:10px; margin-bottom:10px;box-shadow:0px 0px 5px rgba(0,0,0,0.2)'>
    <span style='color:#404040; font-weight:bold'>{$notes[i].last_name} {$notes[i].first_name} {$notes[i].other_names}</span><br/>
    <span style='color:#202020'>{$notes[i].note_time}</span>
    <p style='color:#909090'>{$notes[i].note}</p>
    {if $notes[i].attachments|@sizeof gt 0}
        <div style='background-color:#fafafa; padding:10px'>
        <strong>Attachments</strong><br/>
        <ul style='padding:0px; margin:0px; padding-left:18px; padding-top:5px'>
        {section name=j loop=$notes[i].attachments}
            <li><a style='font-weight:normal' href='/{$notes[i].attachments[j].path}'>{$notes[i].attachments[j].description}</a></li>
        {/section}
        <ul>
        </div>
    {/if}    
    <p><a style='font-size:smaller; color:#d0d0d0' href='{$route}/notes/{$id}/delete/{$notes[i].note_id}'>Delete this note</a></p>
</div>
{/section}
{$form}