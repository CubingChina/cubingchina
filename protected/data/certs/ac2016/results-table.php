<?php if (count($results) > 1): ?>
<div class="<?php echo $hasDetail ? 'has-detail' : 'no-detail'; ?> table-container">
  <table>
    <thead>
      <tr>
        <th>
          项目<br>Event
        </th>
        <th>
          轮次<br>Round
        </th>
        <th>
          排名<br>Rank
        </th>
        <th>
          最好<br>Best
        </th>
        <th>
          平均<br>Average
        </th>
        <?php if ($hasDetail): ?>
        <th class="detail">
          详情<br>Detail
        </th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($results as $result): ?>
      <tr class="<?php echo $this->getRoundClass($result); ?>">
        <td><?php echo $this->getEventImage($result->eventId); ?></td>
        <td class="round"><?php echo $this->getRoundName($result->roundId); ?></td>
        <td class="best"><?php echo $result->pos; ?></td>
        <td class="best"><?php echo $result->getTime('best'); ?></td>
        <td class="best"><?php echo $result->getTime('average'); ?></td>
        <?php if ($hasDetail): ?>
        <td class="detail"><?php echo $result->getDetail(); ?></td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<?php $result = $results[0]; ?>
<div class="<?php echo $hasDetail ? 'has-detail' : 'no-detail'; ?> table-container one-result">
  <table>
    <thead>
      <tr>
        <th>
          项目<br>Event
        </th>
        <th>
          轮次<br>Round
        </th>
        <th>
          排名<br>Rank
        </th>
        <th>
          最好<br>Best
        </th>
        <th>
          平均<br>Average
        </th>
      </tr>
    </thead>
    <tbody>
      <tr class="<?php echo $this->getRoundClass($result); ?>">
        <td><?php echo $this->getEventImage($result->eventId); ?></td>
        <td class="round"><?php echo $this->getRoundName($result->roundId); ?></td>
        <td class="best"><?php echo $result->pos; ?></td>
        <td class="best"><?php echo $result->getTime('best'); ?></td>
        <td class="best"><?php echo $result->getTime('average'); ?></td>
      </tr>
      <tr>
        <td>
          详情<br>Detail
        </td>
        <td colspan="4" class="one-result-detail">
          <?php echo $result->getDetail(); ?>
        </td>
      </tr>
    </tbody>
  </table>
</div>
<?php endif; ?>