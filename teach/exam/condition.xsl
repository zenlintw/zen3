<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" encoding="utf-8" omit-xml-declaration="yes"/>
	<xsl:template match="wm_immediate_random_generate_qti">
		<table border="0" cellpadding="3" cellspacing="1" width="960" style="border-collapse: collapse" class="box01">
			<tr class="bg02 font01">
				<td class="cssTd" colspan="3" align="right">
					<input type="button" value="{$MSG['cancel'][$sysSession->lang]}" class="cssBtn" onclick="location.replace('exam_maintain.php');"/>
					<input type="button" value="{$MSG['prev_step'][$sysSession->lang]}" class="cssBtn" onclick="switchTab(0);"/>
					<input type="button" value="{$MSG['next_step'][$sysSession->lang]}" class="cssBtn" onclick="switchTab(4);"/>
				</td>
			</tr>
			<tr class="bg03 font01">
				<td class="cssTd" colspan="3">
					{$MSG['message'][$sysSession->lang]}
				</td>
			</tr>
			<tr class="bg04 font01">
				<td class="cssTd" align="right">{$MSG['msg_random'][$sysSession->lang]}</td>
				<td class="cssTd" colspan="2">
					<input type="checkbox" name="immediate_random_pick" value="immediate_random_pick" onclick="randomCheck2(this);" checked="checked"/>{$MSG['total'][$sysSession->lang]}
    				<xsl:element name="input">
						<xsl:attribute name="readonly">readonly</xsl:attribute>
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">immediate_random_pick_amount</xsl:attribute>
						<xsl:attribute name="id">immediate_random_pick_amount</xsl:attribute>
						<xsl:attribute name="style">background: transparent;box-shadow: transparent 0 0 0 inset;border: 0px;cursor: default;text-align: center;</xsl:attribute>
						<xsl:attribute name="value"><xsl:value-of select="form/amount"/></xsl:attribute>
						<xsl:attribute name="class">cssInput</xsl:attribute>
						<xsl:attribute name="readonly">readonly</xsl:attribute>
						<xsl:attribute name="size">5</xsl:attribute>
						<xsl:attribute name="onchange">typeCheck(this, 'int');if (parseInt(this.value)>MaxPickedNum){alert(msg_overNumber); this.value=MaxPickedNum; this.form.immediate_random_pick_amount.focus();event.cancelBubble = true;}</xsl:attribute>
					</xsl:element>{$MSG['item'][$sysSession->lang]}
					<input type="button" value="{$MSG['msg_more'][$sysSession->lang]}" class="cssBtn" onclick="createRadomItem(this);"/>
				</td>
			</tr>
			<tr>
				<td colspan="3" width="100%" style="padding: 0">
					<xsl:for-each select="form/conditions/condition">
						<xsl:call-template name="condition"/>
					</xsl:for-each>
				</td>
			</tr>
			<tr class="bg04 font01">
				<td class="cssTd" align="right">{$MSG['score_assigned1'][$sysSession->lang]}</td>
				<td class="cssTd" colspan="2">{$MSG['total_score'][$sysSession->lang]}
		    		<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">immediate_random_pick_score</xsl:attribute>
                                                <xsl:attribute name="id">immediate_random_pick_score</xsl:attribute>
						<xsl:attribute name="value"><xsl:value-of select="form/score"/></xsl:attribute>
						<xsl:attribute name="class">cssInput</xsl:attribute>
						<xsl:attribute name="size">5</xsl:attribute>
					</xsl:element>
				</td>
			</tr>
			<tr class="bg02 font01">
				<td class="cssTd" colspan="3" align="right">
					<input type="button" value="{$MSG['cancel'][$sysSession->lang]}" class="cssBtn" onclick="location.replace('exam_maintain.php');"/>
					<input type="button" value="{$MSG['prev_step'][$sysSession->lang]}" class="cssBtn" onclick="switchTab(0);"/>
					<input type="button" value="{$MSG['next_step'][$sysSession->lang]}" class="cssBtn" onclick="switchTab(4);"/>
				</td>
			</tr>
			<tr class="cssTr">
				<td class="cssTd" width="140"/>
				<td class="cssTd" width="480"/>
				<td class="cssTd" width="180"/>
			</tr>
		</table>
	</xsl:template>
	<xsl:template name="condition">
		<table width="100%">
		
			<tr class="bg03 font01">
				<td class="cssTd" colspan="3">
				<hr style='border-top: 1px solid ;'></hr>
				</td>
			</tr>
			
			<tr class="bg03 font01">
				<td class="cssTd" align="right" width="89" rowspan="3">{$MSG['search_proviso'][$sysSession->lang]}
				<br/>
				<input type="button" value="{$MSG['msg_cut'][$sysSession->lang]}" class="cssBtn" name="cutRad" />

				</td>
				<td class="cssTd" colspan="2">
					<xsl:element name="input">
						<xsl:attribute name="type">checkbox</xsl:attribute>
						<xsl:attribute name="name">isVersion</xsl:attribute>
						<xsl:attribute name="value">ON</xsl:attribute>
						<xsl:if test="version[@selected = 'true']">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</xsl:element>{$MSG['version'][$sysSession->lang]}
					<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">version</xsl:attribute>
						<xsl:attribute name="value"><xsl:value-of select="version"/></xsl:attribute>
						<xsl:attribute name="class">cssInput</xsl:attribute>
						<xsl:attribute name="size">10</xsl:attribute>
						<xsl:attribute name="onkeyup">checkSelect(this);</xsl:attribute>
					</xsl:element>
					<xsl:text>  </xsl:text>
					<xsl:element name="input">
						<xsl:attribute name="type">checkbox</xsl:attribute>
						<xsl:attribute name="name">isVolume</xsl:attribute>
						<xsl:attribute name="value">ON</xsl:attribute>
						<xsl:if test="volume[@selected = 'true']">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</xsl:element>{$MSG['volume'][$sysSession->lang]}
					<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">volume</xsl:attribute>
						<xsl:attribute name="value"><xsl:value-of select="volume"/></xsl:attribute>
						<xsl:attribute name="class">cssInput</xsl:attribute>
						<xsl:attribute name="size">10</xsl:attribute>
						<xsl:attribute name="onkeyup">checkSelect(this);</xsl:attribute>
					</xsl:element>
					<xsl:text>  </xsl:text>
					<xsl:element name="input">
						<xsl:attribute name="type">checkbox</xsl:attribute>
						<xsl:attribute name="name">isChapter</xsl:attribute>
						<xsl:attribute name="value">ON</xsl:attribute>
						<xsl:if test="chapter[@selected = 'true']">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</xsl:element>{$MSG['chapter'][$sysSession->lang]}
					<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">chapter</xsl:attribute>
						<xsl:attribute name="value"><xsl:value-of select="chapter"/></xsl:attribute>
						<xsl:attribute name="class">cssInput</xsl:attribute>
						<xsl:attribute name="size">10</xsl:attribute>
						<xsl:attribute name="onkeyup">checkSelect(this);</xsl:attribute>
					</xsl:element>
					<xsl:text>  </xsl:text>
					<xsl:element name="input">
						<xsl:attribute name="type">checkbox</xsl:attribute>
						<xsl:attribute name="name">isParagraph</xsl:attribute>
						<xsl:attribute name="value">ON</xsl:attribute>
						<xsl:if test="paragraph[@selected = 'true']">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</xsl:element>{$MSG['paragraph'][$sysSession->lang]}
					<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">paragraph</xsl:attribute>
						<xsl:attribute name="value"><xsl:value-of select="paragraph"/></xsl:attribute>
						<xsl:attribute name="class">cssInput</xsl:attribute>
						<xsl:attribute name="size">10</xsl:attribute>
						<xsl:attribute name="onkeyup">checkSelect(this);</xsl:attribute>
					</xsl:element>
					<xsl:text>  </xsl:text>
					<xsl:element name="input">
						<xsl:attribute name="type">checkbox</xsl:attribute>
						<xsl:attribute name="name">isSection</xsl:attribute>
						<xsl:attribute name="value">ON</xsl:attribute>
						<xsl:if test="section[@selected = 'true']">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</xsl:element>{$MSG['section'][$sysSession->lang]}
					<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">section</xsl:attribute>
						<xsl:attribute name="value"><xsl:value-of select="section"/></xsl:attribute>
						<xsl:attribute name="class">cssInput</xsl:attribute>
						<xsl:attribute name="size">10</xsl:attribute>
						<xsl:attribute name="onkeyup">checkSelect(this);</xsl:attribute>
					</xsl:element>
				</td>
			</tr>
			<tr class="bg04 font01">
				<td class="cssTd" colspan="2">
					<table class="font01" style="display: inline">
						<tr>
							<td>
								<xsl:element name="input">
									<xsl:attribute name="type">checkbox</xsl:attribute>
									<xsl:attribute name="name">isType</xsl:attribute>
									<xsl:attribute name="value">ON</xsl:attribute>
									<xsl:if test="type[@selected = 'true']">
										<xsl:attribute name="checked">checked</xsl:attribute>
									</xsl:if>
								</xsl:element>{$MSG['item_type'][$sysSession->lang]}
							</td>
							<td>
                                <xsl:element name="span">
                                    <xsl:attribute name="class">item_type_1</xsl:attribute>
                                    <xsl:element name="input">
                                        <xsl:attribute name="type">checkbox</xsl:attribute>
                                        <xsl:attribute name="name">type[]</xsl:attribute>
                                        <xsl:attribute name="value">1</xsl:attribute>
                                        <xsl:attribute name="onclick">checkSelect2(this)</xsl:attribute>
                                        <xsl:if test="contains(type, '1')">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:if>
                                    </xsl:element>{$MSG['item_type1'][$sysSession->lang]}<br/>
                                </xsl:element>
                                <xsl:element name="span">
                                    <xsl:attribute name="class">item_type_2</xsl:attribute>
                                    <xsl:element name="input">
                                        <xsl:attribute name="type">checkbox</xsl:attribute>
                                        <xsl:attribute name="name">type[]</xsl:attribute>
                                        <xsl:attribute name="value">2</xsl:attribute>
                                        <xsl:attribute name="onclick">checkSelect2(this)</xsl:attribute>
                                        <xsl:if test="contains(type, '2')">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:if>
                                    </xsl:element>{$MSG['item_type2'][$sysSession->lang]}<br/>
                                </xsl:element>
                                <xsl:element name="span">
                                <xsl:attribute name="class">item_type_3</xsl:attribute>
                                    <xsl:element name="input">
                                        <xsl:attribute name="type">checkbox</xsl:attribute>
                                        <xsl:attribute name="name">type[]</xsl:attribute>
                                        <xsl:attribute name="value">3</xsl:attribute>
                                        <xsl:attribute name="onclick">checkSelect2(this)</xsl:attribute>
                                        <xsl:if test="contains(type, '3')">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:if>
                                    </xsl:element>{$MSG['item_type3'][$sysSession->lang]}<br/>
                                </xsl:element>
                                <xsl:element name="span">
                                <xsl:attribute name="class">item_type_4</xsl:attribute>
                                    <xsl:element name="input">
                                        <xsl:attribute name="type">checkbox</xsl:attribute>
                                        <xsl:attribute name="name">type[]</xsl:attribute>
                                        <xsl:attribute name="value">4</xsl:attribute>
                                        <xsl:attribute name="onclick">checkSelect2(this)</xsl:attribute>
                                        <xsl:if test="contains(type, '4')">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:if>
                                    </xsl:element>{$MSG['item_type4'][$sysSession->lang]}<br/>
                                </xsl:element>
                                <xsl:element name="span">
                                <xsl:attribute name="class">item_type_5</xsl:attribute>
                                    <xsl:element name="input">
                                        <xsl:attribute name="type">checkbox</xsl:attribute>
                                        <xsl:attribute name="name">type[]</xsl:attribute>
                                        <xsl:attribute name="value">5</xsl:attribute>
                                        <xsl:attribute name="onclick">checkSelect2(this)</xsl:attribute>
                                        <xsl:if test="contains(type, '5')">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:if>
                                    </xsl:element>{$MSG['item_type5'][$sysSession->lang]}<br/>
                                </xsl:element>
                                <xsl:element name="span">
                                <xsl:attribute name="class">item_type_6</xsl:attribute>
                                    <xsl:element name="input">
                                        <xsl:attribute name="type">checkbox</xsl:attribute>
                                        <xsl:attribute name="name">type[]</xsl:attribute>
                                        <xsl:attribute name="value">6</xsl:attribute>
                                        <xsl:attribute name="onclick">checkSelect2(this)</xsl:attribute>
                                        <xsl:if test="contains(type, '6')">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:if>
                                    </xsl:element>{$MSG['item_type6'][$sysSession->lang]}<br/>
                                </xsl:element>
							</td>
							<td width="100"></td>
							<td>
                                <xsl:element name="input">
                                    <xsl:attribute name="type">checkbox</xsl:attribute>
                                    <xsl:attribute name="name">isLevel</xsl:attribute>
                                    <xsl:attribute name="value">ON</xsl:attribute>
                                    <xsl:if test="level[@selected = 'true']">
                                        <xsl:attribute name="checked">checked</xsl:attribute>
                                    </xsl:if>
                                </xsl:element>{$MSG['hard_level'][$sysSession->lang]}
							</td>
							<td>
                                <xsl:element name="span">
                                    <xsl:attribute name="class">item_level_1</xsl:attribute>
                                    <xsl:element name="input">
                                        <xsl:attribute name="type">checkbox</xsl:attribute>
                                        <xsl:attribute name="name">level[]</xsl:attribute>
                                        <xsl:attribute name="value">1</xsl:attribute>
                                        <xsl:attribute name="onclick">checkSelect2(this)</xsl:attribute>
                                        <xsl:if test="contains(level, '1')">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:if>
                                    </xsl:element>{$MSG['hard_level1'][$sysSession->lang]}<br/>
                                </xsl:element>
                                <xsl:element name="span">
                                    <xsl:attribute name="class">item_level_2</xsl:attribute>
                                    <xsl:element name="input">
                                        <xsl:attribute name="type">checkbox</xsl:attribute>
                                        <xsl:attribute name="name">level[]</xsl:attribute>
                                        <xsl:attribute name="value">2</xsl:attribute>
                                        <xsl:attribute name="onclick">checkSelect2(this)</xsl:attribute>
                                        <xsl:if test="contains(level, '2')">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:if>
                                    </xsl:element>{$MSG['hard_level2'][$sysSession->lang]}<br/>
                                </xsl:element>
                                <xsl:element name="span">
                                    <xsl:attribute name="class">item_level_3</xsl:attribute>
                                    <xsl:element name="input">
                                        <xsl:attribute name="type">checkbox</xsl:attribute>
                                        <xsl:attribute name="name">level[]</xsl:attribute>
                                        <xsl:attribute name="value">3</xsl:attribute>
                                        <xsl:attribute name="onclick">checkSelect2(this)</xsl:attribute>
                                        <xsl:if test="contains(level, '3')">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:if>
                                    </xsl:element>{$MSG['hard_level3'][$sysSession->lang]}<br/>
                                </xsl:element>
                                <xsl:element name="span">
                                    <xsl:attribute name="class">item_level_4</xsl:attribute>
                                    <xsl:element name="input">
                                        <xsl:attribute name="type">checkbox</xsl:attribute>
                                        <xsl:attribute name="name">level[]</xsl:attribute>
                                        <xsl:attribute name="value">4</xsl:attribute>
                                        <xsl:attribute name="onclick">checkSelect2(this)</xsl:attribute>
                                        <xsl:if test="contains(level, '4')">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:if>
                                    </xsl:element>{$MSG['hard_level4'][$sysSession->lang]}<br/>
                                </xsl:element>
                                <xsl:element name="span">
                                    <xsl:attribute name="class">item_level_5</xsl:attribute>
                                    <xsl:element name="input">
                                        <xsl:attribute name="type">checkbox</xsl:attribute>
                                        <xsl:attribute name="name">level[]</xsl:attribute>
                                        <xsl:attribute name="value">5</xsl:attribute>
                                        <xsl:attribute name="onclick">checkSelect2(this)</xsl:attribute>
                                        <xsl:if test="contains(level, '5')">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:if>
                                    </xsl:element>{$MSG['hard_level5'][$sysSession->lang]}<br/>
                                </xsl:element>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="bg03 font01">
				<td class="cssTd" colspan="2">
					<xsl:element name="input">
						<xsl:attribute name="type">checkbox</xsl:attribute>
						<xsl:attribute name="name">isFulltext</xsl:attribute>
						<xsl:attribute name="value">ON</xsl:attribute>
						<xsl:if test="fulltext[@selected = 'true']">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</xsl:element>{$MSG['key_words'][$sysSession->lang]}
					<xsl:element name="input">
						<xsl:attribute name="type">text</xsl:attribute>
						<xsl:attribute name="name">fulltext</xsl:attribute>
						<xsl:attribute name="value"><xsl:value-of select="fulltext"/></xsl:attribute>
						<xsl:attribute name="size">30</xsl:attribute>
						<xsl:attribute name="class">cssInput</xsl:attribute>
						<xsl:attribute name="onfocus">this.value='';</xsl:attribute>
						<xsl:attribute name="onkeyup">checkSelect(this);</xsl:attribute>
					</xsl:element>
				</td>
			</tr>
			<tr class="bg03 font01">
                <td class="cssTd" colspan="2">
                    {$MSG['co_random_amonut'][$sysSession->lang]}
                    <xsl:element name="input">
                        <xsl:attribute name="type">text</xsl:attribute>
                        <xsl:attribute name="name">eachRandomAmount</xsl:attribute>
                        <xsl:attribute name="value"><xsl:value-of select="each_random_amount"/></xsl:attribute>
                        <xsl:attribute name="size">5</xsl:attribute>
                        <xsl:attribute name="class">box02</xsl:attribute>
                        <xsl:attribute name="onkeypress">intOnly();</xsl:attribute>
                        <xsl:attribute name="onchange">typeCheck(this, 'int');if(this.value.length == 0){this.value=0;}checkRandomAmount();</xsl:attribute>
                    </xsl:element>
                    {$MSG['co_random_amonut1'][$sysSession->lang]}
                </td>
            </tr>
		</table>
	</xsl:template>
</xsl:stylesheet>
