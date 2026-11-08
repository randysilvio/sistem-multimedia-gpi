@php
    $builder = app(\App\Services\SlideBuilder::class);
    $customByItem = $schedule->customSlides->groupBy('liturgy_item_id');
@endphp
<form id="liveForm" action="{{ route('liturgy.update', $schedule->id) }}" method="POST" autocomplete="off">
    @csrf

    <div class="ed-card">
        <div class="ed-card-title">Keterangan Ibadah</div>
        <label class="ed-label" for="ed-theme">Tema</label>
        <input id="ed-theme" class="ed-input" name="theme" value="{{ $schedule->theme }}" placeholder="Tema ibadah">
        <label class="ed-label" for="ed-preacher">Pelayan Firman</label>
        <input id="ed-preacher" class="ed-input" name="preacher_name" value="{{ $schedule->preacher_name }}" placeholder="Nama pelayan firman">
    </div>

    @foreach($schedule->liturgy->items as $item)
        @php
            $detail = $schedule->details->firstWhere('liturgy_item_id', $item->id);
            $val = $detail ? $detail->dynamic_content : ($item->static_content ?? '');
            $cleanTitle = $builder->cleanTitle($item->title);
            $type = \App\Services\SlideBuilder::itemType($item->title);
            $typeLabel = ['nyanyian' => 'Nyanyian', 'alkitab' => 'Alkitab', 'teks' => 'Teks'][$type];
            $useCamera = is_array($val) && filter_var($val['use_camera'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $id = $item->id;
        @endphp

        <div class="ed-card" id="ed-item-{{ $id }}">
            <div class="ed-card-title">
                <span>{{ $cleanTitle ?: 'Slide Instruksi' }}</span>
                <span class="ed-type ed-type--{{ $type }}">{{ $typeLabel }}</span>
            </div>

            @if($item->is_dynamic)
                @if($type === 'nyanyian')
                    <div class="ed-row">
                        <select id="buku-lagu-{{ $id }}" class="ed-input ed-w-sm" aria-label="Buku lagu">
                            <option value="KJ">KJ</option><option value="NKB">NKB</option><option value="PKJ">PKJ</option><option value="NR">NR</option><option value="DSL">DSL</option><option value="GPM">GPM</option><option value="BEBAS">Lain</option>
                        </select>
                        <input id="nomor-lagu-{{ $id }}" class="ed-input" placeholder="Nomor lagu" inputmode="numeric">
                        <button type="button" class="ed-btn" data-action="tarik-lagu" data-item="{{ $id }}">Tarik</button>
                    </div>
                    <input class="ed-input" name="dynamic_content[{{ $id }}][judul]" value="{{ is_array($val) ? ($val['judul'] ?? '') : '' }}" placeholder="Judul nyanyian">

                    <div class="ed-baits" id="bait-container-{{ $id }}" data-item="{{ $id }}">
                        @php $baits = (is_array($val) && !empty($val['bait']) && is_array($val['bait'])) ? $val['bait'] : ['1' => '']; @endphp
                        @foreach($baits as $key => $baitText)
                            @php
                                $isReff = (is_string($key) && stripos($key, 'ref') !== false) || preg_match('/^\[?reff?\]?[\s\:\.\-]?/i', (string) $baitText);
                                $label = $isReff ? 'Reff' : (preg_replace('/[^0-9]/', '', (string) $key) ?: $key);
                                $formKey = ($isReff && stripos((string) $key, 'ref') === false) ? 'ref_' . preg_replace('/[^A-Za-z0-9]/', '', (string) $key) : $key;
                                $text = trim(preg_replace('/^\[?REFF\]?\s*/i', '', (string) $baitText));
                            @endphp
                            <div class="ed-bait">
                                <input class="ed-bait-label {{ $isReff ? 'is-reff' : '' }}" value="{{ $label }}" aria-label="Nama bait" title="Ketik angka bait, atau 'Reff'">
                                <textarea class="ed-input" rows="3" name="dynamic_content[{{ $id }}][bait][{{ $formKey }}]">{{ $text }}</textarea>
                                <button type="button" class="ed-x" data-action="hapus" title="Hapus bait">&times;</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="ed-add" data-action="tambah-bait" data-item="{{ $id }}">+ Tambah bait</button>

                @elseif($type === 'alkitab')
                    @php
                        $bibleRef = is_array($val) ? ($val['ref'] ?? '') : '';
                        $bibleText = is_array($val) ? ($val['content'] ?? '') : (string) $val;
                    @endphp
                    <div class="ed-row">
                        <input id="input-alkitab-{{ $id }}" class="ed-input" name="dynamic_content[{{ $id }}][ref]" value="{{ $bibleRef }}"
                               placeholder="Contoh: Yoh 3:16-18 · Mzm 23 · Yes 45:1-8; Mat 5:1-12" data-bible-input="{{ $id }}">
                        <button type="button" class="ed-btn" data-action="tarik-alkitab" data-item="{{ $id }}">Tarik</button>
                    </div>
                    <div class="ed-hint" id="alkitab-status-{{ $id }}">Alkitab TB. Enter = tarik ayat. Kosongkan isi bila layar cukup menampilkan referensinya saja.</div>
                    <textarea id="textarea-{{ $id }}" class="ed-input" rows="6" name="dynamic_content[{{ $id }}][content]" placeholder="16 Karena begitu besar kasih Allah ...">{{ $bibleText }}</textarea>

                @else
                    @php
                        $customTitle = is_array($val) ? ($val['custom_title'] ?? '') : $cleanTitle;
                        $textContent = is_array($val) ? ($val['content'] ?? '') : $val;
                    @endphp
                    <input class="ed-input" name="dynamic_content[{{ $id }}][custom_title]" value="{{ $customTitle }}" placeholder="Judul (kosongkan = teks instruksi besar di tengah)">
                    <textarea class="ed-input" rows="3" name="dynamic_content[{{ $id }}][content]" placeholder="Isi teks. Awali bagian dengan [PNT] atau [JEMAAT] untuk label pembicara.">{{ $textContent }}</textarea>
                @endif

                <label class="ed-switch">
                    <input type="hidden" name="dynamic_content[{{ $id }}][use_camera]" value="0">
                    <input type="checkbox" name="dynamic_content[{{ $id }}][use_camera]" value="1" @checked($useCamera)>
                    <span>Latar kamera live</span>
                </label>

                <div class="ed-customs" id="custom-slide-container-{{ $id }}">
                    @foreach($customByItem->get($id, collect()) as $cSlide)
                        @if($cSlide->isSong())
                            <div class="ed-custom ed-custom--song">
                                <input type="hidden" name="custom_slides[{{ $id }}][{{ $cSlide->id }}][kind]" value="song">
                                <input type="hidden" name="custom_slides[{{ $id }}][{{ $cSlide->id }}][title]" value="{{ $cSlide->title }}">
                                <input type="hidden" name="custom_slides[{{ $id }}][{{ $cSlide->id }}][content]" value="{{ $cSlide->content }}">
                                <div class="ed-row ed-row--between">
                                    <span>Lagu: <b>{{ $cSlide->songData()['judul'] }}</b> <span class="ed-hint">lagu tambahan · bait {{ implode(', ', array_filter(array_keys($cSlide->songData()['bait']), fn ($k) => !str_starts_with((string) $k, 'ref'))) }}</span></span>
                                    <button type="button" class="ed-link-danger" data-action="hapus">Hapus</button>
                                </div>
                            </div>
                            @continue
                        @endif
                        @php
                            $isCam = str_contains((string) $cSlide->content, '[KAMERA_AKTIF]');
                            $cContent = str_replace('[KAMERA_AKTIF]', '', (string) $cSlide->content);
                        @endphp
                        <div class="ed-custom">
                            <input class="ed-input" name="custom_slides[{{ $id }}][{{ $cSlide->id }}][title]" value="{{ $builder->cleanTitle($cSlide->title) }}" placeholder="Judul slide ekstra">
                            <textarea class="ed-input" rows="2" name="custom_slides[{{ $id }}][{{ $cSlide->id }}][content]" placeholder="Isi teks">{{ $cContent }}</textarea>
                            <div class="ed-row ed-row--between">
                                <label class="ed-switch">
                                    <input type="hidden" name="custom_slides[{{ $id }}][{{ $cSlide->id }}][use_camera]" value="0">
                                    <input type="checkbox" name="custom_slides[{{ $id }}][{{ $cSlide->id }}][use_camera]" value="1" @checked($isCam)>
                                    <span>Kamera</span>
                                </label>
                                <button type="button" class="ed-link-danger" data-action="hapus">Hapus</button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="ed-add" data-action="tambah-slide" data-item="{{ $id }}">+ Sisipkan slide ekstra</button>
            @else
                <textarea class="ed-input" rows="2" name="dynamic_content[{{ $id }}]" readonly>{{ $item->static_content }}</textarea>
            @endif
        </div>
    @endforeach
</form>
