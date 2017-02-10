<div id="simplebb-addnews">
	<h3><i class="fa fa-folder-open"></i>Nouveau sujet dans : "{selected-cat}"</h3>
	<div class="simplebb-addcont">
		<div class="simplebb-add">
			<div class="simplebb-addtitle">
				<label for="title"><i class="fa fa-question-circle"></i>Titre du sujet :</label>
				<div class="clr"></div>
				<input type="text" id="title" name="title" value="{title}" maxlength="200" />
			</div>[urltag]
			<div class="simplebb-addurl">
				<label for="alt_name"><i class="fa fa-question-circle"></i>URL :</label>
				<div class="clr"></div>
				<input type="text" id="alt_name" name="alt_name" value="{alt-name}" maxlength="150" />
			</div>[/urltag]
			<div class="clr"></div>
			<div id="related_news"></div>
		</div>
		<div class="simplebb-add">
			<h3><i class="fa fa-question-circle"></i>Sujet :</h3>[not-wysywyg]
			<div class="bb-editor">
				{bbcode}
				<textarea name="full_story" id="full_story" onfocus="setFieldName(this.name)" rows="20" class="f_textarea" >{full-story}</textarea>
			</div>[/not-wysywyg]
			<div id="short" style="display: none">{shortarea}</div>
			{fullarea}
		</div>
		<div class="simplebb-add">
			<table cellpadding="6" cellspacing="1" border="0">
				{xfields}
			</table>
		</div>
		<div class="simplebb-add">
			<div>
				<label for="tags"><i class="fa fa-question-circle"></i>Mots clés</label>
				<div class="clr"></div>
				<input type="text" name="tags" id="tags" value="{tags}" maxlength="150" autocomplete="on" />
			</div>
		</div>
		<table>
			[question]
			<tr>
				<td class="label">
					Question :
				</td>
				<td>
					<div>{question}</div>
				</td>
			</tr>
			<tr>
				<td class="label">
					Réponse :<span class="impot">*</span>
				</td>
				<td>
					<div><input type="text" name="question_answer" class="f_input" /></div>
				</td>
			</tr>
			[/question]
			[sec_code]
			<tr>
				<td class="label">
					Code de l'image <br />girin:<span class="impot">*</span>
				</td>
				<td>
					<div>{sec_code}</div>
					<div><input type="text" name="sec_code" id="sec_code" style="width:115px" class="f_input" /></div>
				</td>
			</tr>
			[/sec_code]
			[recaptcha]
			<tr>
				<td class="label">
					Tapez les mots affichés dans cette image :<span class="impot">*</span>
				</td>
				<td>
					<div>{recaptcha}</div>
				</td>
			</tr>
			[/recaptcha]
			<tr>
				<td colspan="2">{admintag}</td>
			</tr>
		</table>
	</div>
	<div class="simplebb-addbtn">
		<button name="add" class="simplebb-btn simplebb-btnbig" type="submit"><span><i class="fa fa-sign-in"></i>ENVOYER</span></button>
		<button name="nview" onclick="preview()" class="simplebb-btn simplebb-btnbig" type="submit"><span><i class="fa fa-eye"></i>APERÇU</span></button>
	</div>
</div>
