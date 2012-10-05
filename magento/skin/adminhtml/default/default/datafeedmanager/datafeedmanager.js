document.observe('click',function(e){
		
	if(e.findElement('input[type=checkbox]')){ 
		i=e.findElement('input[type=checkbox]');
		
		i.ancestors().each(function(a){
			if(a.hasClassName('fieldset')) 	selector=$(a.id);
		})
		if(selector.id=='attributes-selector'){
			if(i.checked==true)	i.ancestors()[1].select('div')[0].select('INPUT:not(INPUT[type=checkbox]),SELECT').each(function(h){h.disabled=false})
			else i.ancestors()[1].select('div')[0].select('INPUT:not(INPUT[type=checkbox]),SELECT').each(function(h){h.disabled=true})
		}
			
		i.ancestors()[1].select('li').each(function(li){
			if(i.checked==true) {
				li.select('INPUT')[0].checked=true;
			}
			else {
				li.select('INPUT')[0].checked=false;
			}
		})

		
		
		setValues(selector);
		
		
		selector.select('.selected').each(function(s){s.removeClassName('selected')})
		selector.select('.node').each(function(li){
			if(li.select('INPUT')[0].checked==true){
				li.addClassName('selected');
				
			}
		})
	}
})
document.observe('dom:loaded', function(){
	$$('.mapping').each(function(m){
		m.observe('focus',function(e){
			if(m.value.trim()==dfm.mappingStr){
				m.value='';
				m.setStyle({color:'green'})
				
			}
			setValues($('category-selector'));
		})
		m.observe('blur',function(e){
			if(m.value.trim()=='' || m.value.trim()==dfm.mappingStr){
				m.value=dfm.mappingStr;
				m.setStyle({color:'grey'})
				
			}
			setValues($('category-selector'));
		})
	})

	if($('datafeedmanager_categories').value!="*" && $('datafeedmanager_categories').value!=""){
		attributes=$('datafeedmanager_categories').value.evalJSON();
	
		attributes.each(function(attribute){
		
			if(attribute.checked){
				$('category_'+attribute.line).checked=true;
				$('category_'+attribute.line).ancestors()[1].addClassName('selected');
			}
			if(attribute.mapping!=""){
				$('category_'+attribute.line).next().next().value=attribute.mapping;
				$('category_'+attribute.line).next().next().setStyle({color:'green'})				
			}
			else $('category_'+attribute.line).next().next().value=dfm.mappingStr;
                        if(attribute.checked || attribute.mapping!=''){
                            $('category_'+attribute.line).ancestors()[2].visible();
                            if($('category_'+attribute.line).ancestors()[2].previous())
                                $('category_'+attribute.line).ancestors()[2].previous().select('.tree_view')[0].addClassName('open');
                        }
		});
		$$('.node').each(function(n){
			if(n.select("ul")[0] && n.select('.tree_view.open').length<1){
				n.select("ul")[0].hide();
				n.select('.tree_view')[0].addClassName('close');
			}
			else if (n.select("ul")[0]){
				 n.select('.tree_view')[0].addClassName('open');
			}
		})
	}
	else{
		$$('.mapping').each(function(m){
			m.value=dfm.mappingStr;
		})
		$$('.node').each(function(n){
			if(n.select("ul")[0]){
				n.select('.tree_view')[0].addClassName('close');
				 n.select("ul")[0].hide();
			}
		})
	}
       
        $$('.node').each(function(n){
             if(n.select('.tree_view')[0]){
                 n.select('.tree_view')[0].observe('click',function(){
                   if(n.select('.tree_view')[0].hasClassName('open')){
                        if(n.select("ul")[0]) n.select("ul")[0].hide();
                        n.select('.tree_view')[0].removeClassName('open').addClassName('close');
                    }
                    else{

                        if(n.select("ul")[0]) n.select("ul")[0].show();
                         n.select('.tree_view')[0].removeClassName('close').addClassName('open');

                    }
                })
             }
        })

	$('datafeedmanager_type_ids').value.split(',').each(function(e){
		$('type_id_'+e).checked=true;
		$('type_id_'+e).ancestors()[1].addClassName('selected');
	});
	
	$('datafeedmanager_visibility').value.split(',').each(function(e){
		$('visibility_'+e).checked=true;
		$('visibility_'+e).ancestors()[1].addClassName('selected');
	});

	attributes=$('datafeedmanager_attributes').value.evalJSON();
        if($('datafeedmanager_attributes').value=='')$('datafeedmanager_attributes').value="[]";
	attributes.each(function(attribute){
		
		if(attribute.checked){
			 $('attribute_'+attribute.line).checked=true;
			 $('node_'+attribute.line).addClassName('selected');
			 $('node_'+attribute.line).select('INPUT:not(INPUT[type=checkbox]),SELECT').each(function(h){h.disabled=false})
		}
		$('name_attribute_'+attribute.line).value=attribute.code;
		$('condition_attribute_'+attribute.line).value=attribute.condition;
		$('value_attribute_'+attribute.line).value=attribute.value;
	});
		
})

function setValues(selector){
	selection=new Array;
	selector.select('INPUT[type=checkbox]').each(function(i){
		if(selector.id=='attributes-selector'){
		
			attribute={}
			attribute.line=i.readAttribute('identifier');
			attribute.checked=i.checked;
			attribute.code=i.next().value;
			attribute.condition=i.next().next().value;
			attribute.value=i.next().next().next().value;
			selection.push(attribute);
		}
		else if(selector.id=='category-selector'){
			
				attribute={}
				attribute.line=i.readAttribute('identifier');
				attribute.checked=i.checked;
				attribute.mapping=i.next().next().value;
				if(attribute.mapping.trim()=="" || attribute.mapping.trim()==dfm.mappingStr ) attribute.mapping="";
				selection.push(attribute);
				
			
			
		}
		else if(i.checked==true)selection.push(i.readAttribute('identifier'));
		
	})
	switch(selector.id){
		case 'category-selector': 
			$('datafeedmanager_categories').value=Object.toJSON(selection);
                break;
		case 'type-ids-selector': 
			$('datafeedmanager_type_ids').value=selection.join(',');
		break;
		case 'visibility-selector': 
			$('datafeedmanager_visibility').value=selection.join(',');
		break;
		case 'attributes-selector' :
			$('datafeedmanager_attributes').value=Object.toJSON(selection);
		break;
	}
	
}



var dfm={
		mappingStr:">> Create a category mapping",	
		/*
		 * Passer en mode txt / csv  
		 * 
		 */
		clearFields : function(){
			$('feed_header').value='';
			$('feed_product').value='';
			$('feed_footer').value='';
			
		},
		/*
		 * Passer en mode txt / csv  
		 * 
		 */
		textMode : function(){
			
			$$('.txt-type').each(function(f){
				f.ancestors()[1].show()
				
			})
			$$('.txt-type:not(.feed_protector)').each(function(f){
				f.addClassName('required-entry')
			
			})
			$$('.xml-type').each(function(f){
				f.ancestors()[1].hide()
				f.removeClassName('required-entry')
			})
			
			$('feed_header').ancestors()[1].hide();
			$('feed_product').ancestors()[1].hide();
			
			$$('.fields-mapping').each(function(t){t.remove()});
		
		
			myContent=Builder.node('span',{className:'fields-mapping'},[
			              Builder.node('div',{className:'mapping'},['Mapping']),               
                          Builder.node('ul',{className:'txt-field-box',id:'txt-fields-box'})
					])
			$('feed_include_header').insert({after:myContent});
			
	                input=Builder.node('BUTTON',{className:'add-field ',type:'button',onclick:'dfm.addTextField(\'\',\'\');dfm.update();'},['Add field'])
			$('txt-fields-box').insert({after:input});
	       
			if($('feed_header').value!="" && $('feed_product').value!="")dfm.jsonToTextFields();
			
			$('datafeedmanager_form').addClassName('text')
		},
		
		/*
		 * Ajouter une ligne de champs de textes
		 * 
		 */
		addTextField : function(head, prod){
			input=Builder.node('LI',[ 	
	  			Builder.node('INPUT',{className:'txt-field  header-txt-field input-text', value:head, onkeyup:'dfm.update()'}),
	  			Builder.node('INPUT',{className:'txt-field  product-txt-field input-text',value:prod,onkeyup:'dfm.update()'}),
				Builder.node('BUTTON',{className:'remove-field ',type:'button', onclick:'dfm.removeTextField(this);dfm.update();'},['X']),
				Builder.node('BUTTON',{className:'move-field-up ',type:'button', onclick:'dfm.moveField(this,"up");dfm.update();'}),
				Builder.node('BUTTON',{className:'move-field-down ',type:'button', onclick:'dfm.moveField(this,"down");dfm.update();'}),
  			])
  			input.select('BUTTON')[1].update('&uarr;');
			input.select('BUTTON')[2].update('&darr;');
  			$('txt-fields-box').insert({bottom:input});
		},
		
		/*
		 * Supprimer une ligne de champs de textes
		 * 
		 */
		removeTextField : function(elt){
			elt.ancestors()[0].remove();
		},
		
		/*
		 * D�placer une ligne de champs de textes
		 * 
		 */
		moveField : function(elt,direction){
			
			li=elt.ancestors()[0];
			
			index=$('txt-fields-box').select('LI').indexOf(li);
			if (index>0)  prev=index-1; 
			else prev=1;
			
			if (index<$('txt-fields-box').select('LI').length-1)  next=index+1; 
			else next=$('txt-fields-box').select('LI').length-2;
			
			prevli=$('txt-fields-box').select('LI')[prev];
            nextli=$('txt-fields-box').select('LI')[next];
          
			li.remove();
			
            switch(direction){
				case 'up' :
					prevli.insert({before:li})
				break;
				default : 
					nextli.insert({after:li})
				break;
			
			}
		},
		/*
		 * Parser le json en lignes de champs de textes
		 * 
		 */
		jsonToTextFields : function(){
	
			data=new Object;	
			header=$('feed_header').value.evalJSON().header;
			product=$('feed_product').value.evalJSON().product;
			data.header=header;
			data.product=product;
			
			i=0;
			data.product.each(function(){
				
				dfm.addTextField(data.header[i],data.product[i]);
				i++;
			})
		
			
		},
		/*
		 * Parser les lignes de champs de textes en JSON
		 * 
		 */
		textFieldsToJson : function(){
		
			data=new Object;	
			data.header=new Array;
			c=0;
			$('txt-fields-box').select('INPUT.header-txt-field').each(function(i){
				data.header[c]=i.value;
				c++;
			})
			data.product=new Array;
			c=0;
			$('txt-fields-box').select('INPUT.product-txt-field').each(function(i){
				data.product[c]=i.value;
				c++;
			})
			$('feed_header').value='{"header":'+Object.toJSON(data.header)+"}";
			$('feed_product').value='{"product":'+Object.toJSON(data.product)+"}";
			
		},
		/*
		 * Fournit les caract�res utilis�s pour cr�er le fichier en mode texte
		 * 
		 */
		getTextParams : function(){
			o=new Object;
			o.delimiter=$('feed_separator').value;
			o.enclosure=$('feed_protector').value;
			o.escape="\\";
			return o;
		},
		
		/*
		 * Construit le texte � afficher en preview
		 * 
		 */
		previewTextFile : function(value){
			txt=dfm.getTextParams();
			rtn='';
			data=value.evalJSON()
			if(typeof data.header!='undefined') data=data.header;
			else data=data.product;
			i=0;
			
			data.each(function(o){
				if(txt.delimiter=='\\t')txt.delimiter="     ";
				if(i>0)rtn+=txt.delimiter;
				
				o=dfm.escapeValue(o);
				rtn+=txt.enclosure+o+txt.enclosure;
				i++;
			})
			
			
			return rtn;
		},
		/*
		 * Echappement des caract�res 
		 * 
		 */
		escapeValue : function(value){
			txt=dfm.getTextParams();
			if(txt.enclosure!=''){
				
				if(txt.enclosure=="|"){prot="\\|"; protReplace="\\|"; finalProt="|"}
				else {prot=txt.enclosure; protReplace="\\"+txt.enclosure; finalProt=txt.enclosure;}
				return value.replace(/\\/g,"\\\\").replace(eval("/"+prot+"/g"),protReplace);
			
			}
			else{
				if(txt.delimiter=="|"){sep="\\|"; sepReplace="\\|";}
				else {sep=txt.delimiter; sepReplace="\\"+txt.delimiter;}
				return value.replace(/\\/g,"\\\\").replace(eval("/"+sep+"/g"),sepReplace);
			}
		},
	
	
		/*
		 * Passer en mode xml
		 * 
		 */
		xmlMode : function(){
			
			$$('.fields-mapping').each(function(t){t.remove()});
			
			
			$$('.txt-type').each(function(f){
				f.ancestors()[1].hide();
				f.removeClassName('required-entry')
			})
			$$('.xml-type').each(function(f){
				f.ancestors()[1].show()
				f.addClassName('required-entry')
			})
			
			$('feed_header').ancestors()[1].show();
			$('feed_product').ancestors()[1].show();
			
			$('datafeedmanager_form').removeClassName('text')
		},
		
		/*
		 * Mise � jour des donn�es 
		 * 
		 */
		update:function(){
			// mise � jour des textarea si mode text
			if(!dfm.isXmlMode()) dfm.textFieldsToJson();
			// nom du fichier
			$('dfm-view').select('.feedname')[0].update($('feed_name').value)
			$('dfm-view').select('.feedtype')[0].update($('feed_type').options[$('feed_type').selectedIndex].innerHTML)
			// preview header
			if(!dfm.isXmlMode())value=dfm.enlighter(dfm.previewTextFile($('feed_header').value));
			else value=dfm.enlighter($('feed_header').value);
			
			if(($('feed_include_header').value!=0 && !dfm.isXmlMode()) || dfm.isXmlMode() ) $('dfm-view').select('._header')[0].update(value);
			else $('dfm-view').select('._header')[0].update('')
			
			// preview footer
			if( $('feed_type').value==1) $('dfm-view').select('._footer')[0].update(dfm.enlighter($('feed_footer').value));
			else  $('dfm-view').select('._footer')[0].update('')
			
			// preview product
			if(!dfm.isXmlMode())value=dfm.previewTextFile($('feed_product').value);
			else value=$('feed_product').value;
			
			p='<br><pre class="productpattern">'+dfm.enlighter(value)+'</pre><br>';
			
			
			if(!dfm.isXmlMode()) $('dfm-view').select('._product')[0].update(p+p+p+p+p)
			else  $('dfm-view').select('._product')[0].update(p+p);
			
		},
		/*
		 * Surligenr le code
		 * 
		 */
		enlighter: function(text){
		
			// tags
			text=text.replace(/<([^?^!]{1}|[\/]{1})(.[^>]*)>/g,"<span class='blue'>"+"<$1$2>".escapeHTML()+"</span>")
			
			// comments
			text=text.replace(/<!--/g,"¤");
			text=text.replace(/-->/g,"¤");
			text=text.replace(/¤([^¤]*)¤/g,"<span class='green'>"+"<!--$1-->".escapeHTML()+"</span>");
			
			// php code
			text=text.replace(/<\?/g,"¤");
			text=text.replace(/\?>/g,"¤");
			text=text.replace(/¤([^¤]*)¤/g,"<span class='orange'>"+"<?$1?>".escapeHTML()+"</span>");
			// superattribut
			text=text.replace(/\{(G:[^\s}[:]*)(\sparent|\sgrouped|\sconfigurable|\sbundle)?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?\}/g,"<span class='purple'>{$1<span class='grey'>$2</span>$4<span class='green'>$5</span>$7<span class='green'>$8</span>$10<span class='green'>$11</span>$13<span class='green'>$14</span>$16<span class='green'>$17</span>$19<span class='green'>$20</span>}</span>");
			
			// attributs + 6 options 
			text=text.replace(/\{([^\s}[:]*)(\sparent|\sgrouped|\sconfigurable|\sbundle)?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?\}/g,"<span class='pink'>{$1<span class='grey'>$2</span>$4<span class='green'>$5</span>$7<span class='green'>$8</span>$10<span class='green'>$11</span>$13<span class='green'>$14</span>$16<span class='green'>$17</span>$19<span class='green'>$20</span>}</span>");
					
			// attributs + options bool
			text=text.replace(/\{([^\s}[:]*)(\sparent|\sgrouped|\sconfigurable|\sbundle)?(\?)(\[[^\]]*\])(:)(\[[^\]]*\])\}/g,"<span class='pink'>{$1<span class='grey'>$2</span>$3<span class='green'>$4</span>$5<span class='red'>$6</span>}</span>");
			
			
			return text;
		},		
	
		currentMode:null ,
		/*
		 * Savoir si on est en mode xml ou non
		 * 
		 */
		
		isXmlMode: function (){
			if($('feed_type').value==1) return true;
			else return false
		},
		/*
		 * Renvoie l'id du mode
		 * 
		 */
		
		getIdMode: function (){
			
			return $('feed_type').value;
		},
		/*
		 * R�gle l'id du mode
		 * 
		 */
		
		setIdMode: function (id){
			
			$('feed_type').value=id;
		},
		/*
		 * Changer de mode
		 * 
		 */
		changeMode : function (){
			
			if(dfm.currentMode==null ){
				dfm.currentMode=dfm.getIdMode();
				if(dfm.isXmlMode()) dfm.xmlMode();
				else dfm.textMode();
			
			}
			else{
				if((dfm.currentMode>1 && dfm.getIdMode()==1)|(dfm.currentMode==1 && dfm.getIdMode()>1) ){
					if(confirm("Changing file type from/to xml will clear all your setting.\ Do you want to continue ?")){
						dfm.clearFields();
						if(dfm.isXmlMode()) dfm.xmlMode();
						else dfm.textMode();
						dfm.setIdMode(dfm.getIdMode());
					
					}
					else  dfm.setIdMode(dfm.currentMode);
					dfm.currentMode=dfm.getIdMode();
				}
			}
			dfm.update();
			
		}
		
}
/*
 * OBSERVERS
 * 
 */
document.observe('dom:loaded', function(){
	
	
	page=Builder.node('div',{id:'dfm-view'},[
	       Builder.node('span','Preview : '),                                  
	       Builder.node('span',{className:'feedname'},'exemple'),
	       Builder.node('span','.'),
	       Builder.node('span',{className:'feedtype'},'xml'),
	       Builder.node('div',{id:'page'},[
	         
	          Builder.node('pre',{className:'_header',name:''}),
	          Builder.node('pre',{className:'_product',name:''}),
	          Builder.node('pre',{className:'_footer',name:''})
           
           ])
    ])
    
    $('datafeedmanager_form').select('.hor-scroll')[0].insert({bottom:page});
	
	$('feed_type').observe('change',function(){dfm.changeMode();})
	
	$$('.refresh').each(function(f){
		f.observe('keyup', function(){
			dfm.update()
		})
	 })
	 $$('.refresh').each(function(f){
		f.observe('change', function(){
			dfm.update()
		})
	 })
	dfm.changeMode();
	
	
})