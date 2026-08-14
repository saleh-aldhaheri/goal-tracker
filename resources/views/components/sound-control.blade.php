<div class="snd-wrap">
    <button class="snd-fab" id="sndFab" title="Sound" aria-label="Sound controls">🔊</button>
    <div class="snd-panel" id="sndPanel">
        <h4>Sound</h4>
        <label class="snd-row">
            <span>Music</span>
            <select id="sndSong" class="select">
                <option value="off">Off</option>
                <option value="calm">Calm Day</option>
                <option value="focus">Focus</option>
                <option value="rainy">Rainy</option>
                <option value="night">Night</option>
            </select>
        </label>
        <label class="snd-row"><span>Music volume</span><input type="range" id="sndMusicVol" min="0" max="1" step="0.01"></label>
        <label class="snd-row"><span>SFX</span><input type="checkbox" id="sndSfx"></label>
        <label class="snd-row"><span>SFX volume</span><input type="range" id="sndSfxVol" min="0" max="1" step="0.01"></label>
        <label class="snd-row"><span>Ambience</span><input type="checkbox" id="sndAmb"></label>
        <label class="snd-row"><span>Ambience volume</span><input type="range" id="sndAmbVol" min="0" max="1" step="0.01"></label>
    </div>
</div>
