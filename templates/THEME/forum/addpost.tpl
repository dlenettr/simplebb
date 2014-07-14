<div id="addnews">
	<div class="phead"><h3>Yeni Konu Ekleme Paneli: '{selected-cat}'</h3></div>
	<div class="contain">
		<div class="l">
			<label for="title">Konu Başlığı</label><span><button title="Makalenizi oluşturmadan önce konu başlığınız ile benzer makale olup olmadığını kontrol edin" class="smallbtn wbtn" onclick="find_relates(); return false;"><i class="fa fa-info"></i>Benzer Konuları Göster</button></span>
			<div class="clr"></div>
			<input type="text" id="title" name="title" value="{title}" maxlength="200" />
		</div>[urltag]
		<div class="l" style="margin-left: 20px;">
			<label for="alt_name"><i class="fa fa-question-circle"></i>URL Adresi</label>
			<div class="clr"></div>
			<input type="text" id="alt_name" name="alt_name" value="{alt-name}" maxlength="150" />
		</div>[/urltag]
		<div class="clr"></div>
		<div id="related_news"></div>
		<div style="display:none;"><a href="#" onclick="$('.addvote').toggle();return false;">Anket Ekle</a></div>
		<div class="addvote" style="display:none;">
			<div>
				<label for="vote_title">Anket Başlığı</label>
				<div class="clr"></div>
				<input type="text" id="vote_title" name="vote_title" value="{votetitle}" maxlength="150" />
			</div>
			<div>
				<label for="frage">Anket Sorusu</label>
				<div class="clr"></div>
				<input type="text" id="frage" name="frage" value="{frage}" maxlength="150" />
			</div>
			<div>
				<label for="vote_body">Anket Seçenekleri<br /><br /><span>Her yeni satır anket için ayrı bir seçenek olacak şekilde girin.</span></label>
				<div class="clr"></div>
				<textarea id="vote_body" name="vote_body" rows="10">{votebody}</textarea>
			</div>
			<div class="r">
				<label for="allow_m_vote">Kişilerin birden fazla değerlendirme yapmasına izin ver.</label><input type="checkbox" id="allow_m_vote" name="allow_m_vote" value="1" {allowmvote}>
			</div>
			<div class="clr"></div>
		</div>
	</div>
	<div>
		<h3><i class="fa fa-question-circle"></i>Konu içeriği:</h3>[not-wysywyg]
		<div class="bb-editor">
			{bbcode}
			<textarea name="full_story" id="full_story" onfocus="setFieldName(this.name)" rows="20" class="f_textarea" >{full-story}</textarea>
		</div>[/not-wysywyg]
		{fullarea}
	</div>
	<div class="contain">
		<table cellpadding="6" cellspacing="1" border="0">
			{xfields}
		</table>
	</div>
    <div class="contain">
		<div>
			<label for="tags"><i class="fa fa-question-circle"></i>Etiketler</label>
			<div class="clr"></div>
			<input type="text" name="tags" id="tags" value="{tags}" maxlength="150" autocomplete="on" />
		</div>
	</div>
	<table>
		[question]
		<tr>
			<td class="label">
				Soru:
			</td>
			<td>
				<div>{question}</div>
			</td>
		</tr>
		<tr>
			<td class="label">
				Cevabınız:<span class="impot">*</span>
			</td>
			<td>
				<div><input type="text" name="question_answer" class="f_input" /></div>
			</td>
		</tr>
		[/question]
		[sec_code]
		<tr>
			<td class="label">
				Resimdeki kodu<br />girin:<span class="impot">*</span>
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
				Resimde görünen,<br />iki kelimeyi girin:<span class="impot">*</span>
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
	<div class="fieldsubmit">
		<button name="add" class="btn" type="submit"><span><i class="fa fa-sign-in"></i>Gönder</span></button>
		<button name="nview" onclick="preview()" class="btn" type="submit"><span><i class="fa fa-eye"></i>Göster</span></button>
	</div>
</div>
