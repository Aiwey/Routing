{extends file="main.tpl"}

{block name=content}
    <div class="inner">
    <div class ="content">
        <h2 class ="align-center"> Zaloguj sie </h2>
        <hr />
<form action="{$conf->action_url}login" method="post">
	
	<fieldset>
        <div class ="field">
			<label for="id_login">login: </label>
			<input id="id_login" type="text" name="login"/>
		</div>
        <div>
			<label for="id_pass">pass: </label>
			<input id="id_pass" type="password" name="pass" /><br />
		</div>
		<ul class="actions align-center">
		<li><input value="zaloguj" class="button special" type="submit"></li>
									</ul>
	</fieldset>
</form>	
    </div>
    </div>
{include file='messages.tpl'}

{/block}
