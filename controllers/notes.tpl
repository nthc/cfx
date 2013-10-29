{section name=i loop=$notes}
<div style='background-color:#fff; padding:10px; margin-top:10px; margin-bottom:10px;box-shadow:0px 0px 5px rgba(0,0,0,0.2)'>
    <span style='color:#404040; font-weight:bold'>{$notes[i].last_name} {$notes[i].first_name} {$notes[i].other_names}</span><br/>
    <span style='color:#202020'>{$notes[i].note_time}</span>
    <p style='color:#909090'>{$notes[i].note}</p>
</div>
{/section}
{$form}