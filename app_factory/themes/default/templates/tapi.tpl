
<table class="tapi-table">
	<thead>
	<tr>
		<td><input type='checkbox'/></td>
		{section name=i loop=$headers}
			<td>{$headers[i]}</td>
		{/section}
	</tr>
	</thead>
		{section name=i loop=$data}
			<tr>
				<td><input type='checkbox'/></td>
				{section name=j loop=$data[i]}
				<td>{$data[i][j]}</td>
				{/section}
			</tr>
		{/section}	
	<tbody>
	
	</tbody>
</table>
