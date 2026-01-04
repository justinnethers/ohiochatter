<?php

namespace App\Livewire;

use App\Modules\Pickem\Models\Pickem;
use App\Modules\Pickem\Models\PickemGroup;
use App\Modules\Pickem\Models\PickemMatchup;
use Illuminate\Support\Str;
use Livewire\Component;

class PickemEditor extends Component
{
    public ?Pickem $pickem = null;

    // Pickem fields
    public string $title = '';

    public string $body = '';

    public ?int $pickem_group_id = null;

    public string $scoring_type = 'simple';

    public ?string $picks_lock_at = null;

    public bool $is_finalized = false;

    // Matchups
    public array $matchups = [];

    public bool $showMatchupForm = false;

    public ?int $editingMatchupIndex = null;

    public string $matchupOptionA = '';

    public string $matchupOptionB = '';

    public string $matchupDescription = '';

    public int $matchupPoints = 1;

    protected function rules()
    {
        return [
            'title' => 'required|min:3|max:255',
            'body' => 'nullable|max:5000',
            'pickem_group_id' => 'nullable|exists:pickem_groups,id',
            'scoring_type' => 'required|in:simple,weighted,confidence',
            'picks_lock_at' => 'nullable|date',
        ];
    }

    public function mount(?Pickem $pickem = null)
    {
        $this->checkAdmin();

        if ($pickem && $pickem->exists) {
            $this->pickem = $pickem;
            $this->title = $pickem->title;
            $this->body = $pickem->body ?? '';
            $this->pickem_group_id = $pickem->pickem_group_id;
            $this->scoring_type = $pickem->scoring_type;
            $this->picks_lock_at = $pickem->picks_lock_at?->format('Y-m-d\TH:i');
            $this->is_finalized = $pickem->is_finalized;

            foreach ($pickem->matchups as $matchup) {
                $this->matchups[] = [
                    'id' => $matchup->id,
                    'option_a' => $matchup->option_a,
                    'option_b' => $matchup->option_b,
                    'description' => $matchup->description ?? '',
                    'points' => $matchup->points,
                    'winner' => $matchup->winner,
                ];
            }
        }
    }

    protected function checkAdmin()
    {
        if (! auth()->user()?->is_admin) {
            abort(403);
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'slug' => Str::slug($this->title).'-'.Str::random(5),
            'body' => $this->body ?: null,
            'pickem_group_id' => $this->pickem_group_id,
            'scoring_type' => $this->scoring_type,
            'picks_lock_at' => $this->picks_lock_at ?: null,
            'is_finalized' => $this->is_finalized,
        ];

        if ($this->pickem) {
            // Don't regenerate slug on update
            unset($data['slug']);
            $this->pickem->update($data);
            $pickem = $this->pickem;
        } else {
            $data['user_id'] = auth()->id();
            $pickem = Pickem::create($data);
            $this->pickem = $pickem;
        }

        // Sync matchups
        $existingIds = collect($this->matchups)->pluck('id')->filter()->toArray();

        // Delete removed matchups
        $pickem->matchups()->whereNotIn('id', $existingIds)->delete();

        // Update or create matchups
        foreach ($this->matchups as $index => $matchupData) {
            $matchupFields = [
                'option_a' => $matchupData['option_a'],
                'option_b' => $matchupData['option_b'],
                'description' => $matchupData['description'] ?: null,
                'points' => $matchupData['points'],
                'winner' => $matchupData['winner'],
                'display_order' => $index,
            ];

            if (! empty($matchupData['id'])) {
                PickemMatchup::where('id', $matchupData['id'])->update($matchupFields);
            } else {
                $matchupFields['pickem_id'] = $pickem->id;
                $newMatchup = PickemMatchup::create($matchupFields);
                $this->matchups[$index]['id'] = $newMatchup->id;
            }
        }

        session()->flash('success', $this->pickem->wasRecentlyCreated ? 'Pick \'Em created successfully.' : 'Pick \'Em updated successfully.');

        return redirect()->route('pickem.admin.edit', $pickem);
    }

    public function openMatchupForm(?int $index = null)
    {
        if ($index !== null && isset($this->matchups[$index])) {
            $matchup = $this->matchups[$index];
            $this->editingMatchupIndex = $index;
            $this->matchupOptionA = $matchup['option_a'];
            $this->matchupOptionB = $matchup['option_b'];
            $this->matchupDescription = $matchup['description'];
            $this->matchupPoints = $matchup['points'];
        } else {
            $this->resetMatchupForm();
        }

        $this->showMatchupForm = true;
    }

    public function closeMatchupForm()
    {
        $this->showMatchupForm = false;
        $this->resetMatchupForm();
    }

    public function resetMatchupForm()
    {
        $this->editingMatchupIndex = null;
        $this->matchupOptionA = '';
        $this->matchupOptionB = '';
        $this->matchupDescription = '';
        $this->matchupPoints = 1;
    }

    public function saveMatchup()
    {
        $this->validate([
            'matchupOptionA' => 'required|min:1|max:255',
            'matchupOptionB' => 'required|min:1|max:255',
            'matchupDescription' => 'nullable|max:500',
            'matchupPoints' => 'required|integer|min:1',
        ]);

        $matchupData = [
            'id' => null,
            'option_a' => $this->matchupOptionA,
            'option_b' => $this->matchupOptionB,
            'description' => $this->matchupDescription,
            'points' => $this->matchupPoints,
            'winner' => null,
        ];

        if ($this->editingMatchupIndex !== null) {
            $matchupData['id'] = $this->matchups[$this->editingMatchupIndex]['id'] ?? null;
            $matchupData['winner'] = $this->matchups[$this->editingMatchupIndex]['winner'] ?? null;
            $this->matchups[$this->editingMatchupIndex] = $matchupData;
        } else {
            $this->matchups[] = $matchupData;
        }

        $this->closeMatchupForm();
    }

    public function removeMatchup(int $index)
    {
        unset($this->matchups[$index]);
        $this->matchups = array_values($this->matchups);
    }

    public function moveMatchupUp(int $index)
    {
        if ($index > 0) {
            $temp = $this->matchups[$index - 1];
            $this->matchups[$index - 1] = $this->matchups[$index];
            $this->matchups[$index] = $temp;
        }
    }

    public function moveMatchupDown(int $index)
    {
        if ($index < count($this->matchups) - 1) {
            $temp = $this->matchups[$index + 1];
            $this->matchups[$index + 1] = $this->matchups[$index];
            $this->matchups[$index] = $temp;
        }
    }

    public function setWinner(int $index, ?string $winner)
    {
        if (isset($this->matchups[$index])) {
            $this->matchups[$index]['winner'] = $winner;
        }
    }

    public function finalize()
    {
        $this->is_finalized = true;

        // Auto-lock picks if not already locked
        if (! $this->picks_lock_at || now()->lt($this->picks_lock_at)) {
            $this->picks_lock_at = now()->format('Y-m-d\TH:i');
        }

        $this->save();
    }

    public function render()
    {
        return view('livewire.pickem-editor', [
            'groups' => PickemGroup::orderBy('name')->get(),
        ]);
    }
}
