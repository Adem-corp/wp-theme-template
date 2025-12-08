(function () {
	tinymce.PluginManager.add('adem_buttons', function (editor, url) {
		editor.addButton('adem_buttons', {
			text: 'Доп. элементы',
			type: 'menubutton',
			menu: [
				{
					text: 'Таблица',
					onclick: function () {
						editor.windowManager.open({
							title: 'Вставить адаптивную таблицу',
							body: [
								{
									type: 'textbox',
									name: 'caption',
									label: 'Заголовок (необязательно)',
								},
								{
									type: 'listbox',
									name: 'cols',
									label: 'Кол-во столбцов',
									values: [
										{
											text: '1',
											value: '1'
										},
										{
											text: '2',
											value: '2'
										},
										{
											text: '3',
											value: '3'
										},
										{
											text: '4',
											value: '4'
										},
										{
											text: '5',
											value: '5'
										},
										{
											text: '6',
											value: '6'
										},
										{
											text: '7',
											value: '7'
										},
										{
											text: '8',
											value: '8'
										},
										{
											text: '9',
											value: '9'
										},
										{
											text: '10',
											value: '10'
										},
									]
								},
								{
									type: 'listbox',
									name: 'rows',
									label: 'Кол-во строк',
									values: [
										{
											text: '1',
											value: '1'
										},
										{
											text: '2',
											value: '2'
										},
										{
											text: '3',
											value: '3'
										},
										{
											text: '4',
											value: '4'
										},
										{
											text: '5',
											value: '5'
										},
										{
											text: '6',
											value: '6'
										},
										{
											text: '7',
											value: '7'
										},
										{
											text: '8',
											value: '8'
										},
										{
											text: '9',
											value: '9'
										},
										{
											text: '10',
											value: '10'
										},
									]
								}
							],
							onsubmit: function (e) {
								let table = '';
								let cols = '';
								if (e.data.caption) {
									table += '<caption>' + e.data.caption + '</caption>';
								}
								for (let i = 0; i < e.data.cols; i++) {
									cols += '<td></td>';
								}
								for (let i = 0; i < e.data.rows; i++) {
									table += '<tr>' + cols + '</tr>';
								}
								editor.insertContent('<div class="st-table"><table>' + table + '</table></div>');
							}
						});
					}
				},
			]
		});
	});
})();
