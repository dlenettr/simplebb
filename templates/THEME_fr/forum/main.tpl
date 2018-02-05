<div id="simplebb">
	[forum:main]<h3><i class="fa fa-comments-o"></i>FORUM</h3>[/forum:main]
	[forum:inside]<h3><i class="fa fa-comments-o"></i>{category echo="name"}</h3>[/forum:inside]
	[depth=1]

		[categories]
		<div class="simplebb-category">
			<h3>[link]<i class="fa fa-share-square-o"></i>{title}[/link]</h3>
			<div class="simplebb-csep"></div>
			<div class="simplebb-ftitlebar">
				<div class="simplebb-fticon"></div>
				<div class="simplebb-ftname">
					<span>Forum</span>
				</div>
				<div class="simplebb-ftarticle">
					<span>Sujets</span>
				</div>
				<div class="simplebb-ftcomments">
					<span>Réponses</span>
				</div>
				<div class="simplebb-ftlastmessage">
					<span>Dernier sujet</span>
				</div>
				<div class="clr"></div>
			</div>

			[forums]
				<div class="simplebb-forum">
					<div class="simplebb-ficon">
						<i class="fa fa-envelope"></i>
					</div>
					<div class="simplebb-fname">
						<h3>[link]{title}[/link]<i class="fa fa-rss"></i></h3>
						<span>{metatitle}</span>
					</div>
					<div class="simplebb-farticle">
						<span><i class="fa fa-envelope-o"></i>{posts}</span>
					</div>
					<div class="simplebb-fcomment">
						<span><i class="fa fa-comments-o"></i>{comments}</span>
					</div>
					<div class="simplebb-flastmessage">
						[lastpost]
						<a href="{lastpost-url}"><i class="fa fa-file-text-o"></i>{lastpost}</a>
						<img src="{lastposter-foto}" alt="{lastposter}" />
						<span><i class="fa fa-user"></i>{lastposter}</span>
						<span><i class="fa fa-calendar"></i>{lastpost-date}</span>
						[/lastpost]
						[not-lastpost]
							<span>Pas encore de sujets</span>
						[/not-lastpost]
					</div>
					<div class="clr"></div>
				</div>
			[/forums]

		</div>
		[/categories]

	[/depth=1]

	[depth=2]

		[sub-forums]
		<div class="simplebb-subforums">
			<h3><i class="fa fa-share-square-o"></i> SOUS FORUM</h3>
			<div class="simplebb-csep"></div>
			<ul class="simplebb-subforum-list">
				{subforums}
			</ul>
			<div class="clr"></div>
		</div>
		[/sub-forums]


		<div class="simplebb-category">
			<h3><i class="fa fa-share-square-o"></i>{category echo="name"}</h3>
			<div class="simplebb-csep"></div>
			<div class="simplebb-ttitlebar">
				<div class="simplebb-tticon"></div>
				<div class="simplebb-ttname">
					<span>Sujets</span>
				</div>
				<div class="simplebb-ttarticle">
					<span>Vues</span>
				</div>
				<div class="simplebb-ttcomments">
					<span>Réponses</span>
				</div>
				<div class="simplebb-ttlastmessage">
					<span>Dernière réponse</span>
				</div>
				<div class="clr"></div>
			</div>
			{threads.tpl}
		</div>

	[/depth=2]

	[depth=3]

		<div class="clr"></div>
		<div class="simplebb-naddbtn">
			<a href="/addpost/{category echo="id"}/"><i class="fa fa-pencil-square-o"></i>NOUVEAU</a>
		</div>
		<div class="clr"></div>
		<div class="simplebb-category">
			<h3><i class="fa fa-share-square-o"></i>{category echo="name"}</h3>
			<div class="simplebb-csep"></div>
			<div class="simplebb-ttitlebar">
				<div class="simplebb-tticon"></div>
				<div class="simplebb-ttname">
					<span>Sujets</span>
				</div>
				<div class="simplebb-ttarticle">
					<span>Vues</span>
				</div>
				<div class="simplebb-ttcomments">
					<span>Réponses</span>
				</div>
				<div class="simplebb-ttlastmessage">
					<span>Dernière réponse</span>
				</div>
				<div class="clr"></div>
			</div>
			{threads.tpl}
		</div>

	[/depth=3]

	[depth=4]

		{post.tpl}

	[/depth=4]

</div>

{forum-stats}
